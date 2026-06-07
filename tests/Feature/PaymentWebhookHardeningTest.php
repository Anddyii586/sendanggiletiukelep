<?php

use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\Service;
use App\Models\Ticket;
use App\Models\User;

beforeEach(function (): void {
    config(['midtrans.server_key' => 'phase-2-server-key']);

    $this->service = Service::create([
        'name' => 'Phase 2 Ticket',
        'slug' => 'phase-2-ticket',
        'description' => 'Phase 2 test package',
        'price' => 10000,
        'pricing_type' => 'per_person',
        'is_active' => true,
    ]);
});

function phase2Booking(User $user, Service $service, array $attributes = []): Booking
{
    return Booking::create(array_merge([
        'booking_code' => 'BK-P2-'.strtoupper(str()->random(8)),
        'user_id' => $user->id,
        'service_id' => $service->id,
        'visit_date' => now()->addDay()->toDateString(),
        'participant_count' => 1,
        'contact_name' => $user->name,
        'contact_phone' => '081234567890',
        'contact_email' => $user->email,
        'subtotal' => 10000,
        'service_fee' => 0,
        'total_price' => 10000,
        'status' => Booking::STATUS_WAITING_PAYMENT,
        'expires_at' => now()->addHour(),
    ], $attributes));
}

function phase2Payment(Booking $booking, array $attributes = []): Payment
{
    return $booking->payment()->create(array_merge([
        'order_id' => 'ORDER-'.$booking->booking_code,
        'gross_amount' => $booking->total_price,
        'status' => Payment::STATUS_UNPAID,
        'expired_at' => $booking->expires_at,
    ], $attributes));
}

function phase2Signature(array $payload): string
{
    return hash(
        'sha512',
        $payload['order_id'].$payload['status_code'].$payload['gross_amount'].config('midtrans.server_key')
    );
}

function phase2Payload(Payment $payment, array $overrides = []): array
{
    $payload = array_merge([
        'order_id' => $payment->order_id,
        'status_code' => '200',
        'gross_amount' => (string) $payment->gross_amount,
        'transaction_status' => 'settlement',
        'payment_type' => 'bank_transfer',
    ], $overrides);

    if (! array_key_exists('signature_key', $overrides)) {
        $payload['signature_key'] = phase2Signature($payload);
    }

    return $payload;
}

test('webhook amount mismatch ditolak dan tercatat', function (): void {
    $user = User::factory()->create(['role' => 'user']);
    $booking = phase2Booking($user, $this->service);
    $payment = phase2Payment($booking);
    $payload = phase2Payload($payment, ['gross_amount' => '9999.00']);

    $this->postJson(route('payments.midtrans.notification'), $payload)
        ->assertStatus(400)
        ->assertJson(['message' => 'gross_amount mismatch']);

    expect($payment->fresh()->status)->toBe(Payment::STATUS_UNPAID)
        ->and($booking->fresh()->status)->toBe(Booking::STATUS_WAITING_PAYMENT)
        ->and($booking->ticket()->exists())->toBeFalse();

    $log = PaymentLog::first();

    expect($log)->not->toBeNull()
        ->and($log->payment_id)->toBe($payment->id)
        ->and($log->order_id)->toBe($payment->order_id)
        ->and($log->signature_valid)->toBeTrue()
        ->and($log->error_message)->toBe('gross_amount mismatch')
        ->and($log->processed_at)->not->toBeNull();
});

test('settlement webhook menyimpan payment_log', function (): void {
    $user = User::factory()->create(['role' => 'user']);
    $booking = phase2Booking($user, $this->service);
    $payment = phase2Payment($booking);

    $this->postJson(route('payments.midtrans.notification'), phase2Payload($payment))
        ->assertOk();

    $log = PaymentLog::first();

    expect($log)->not->toBeNull()
        ->and($log->payment_id)->toBe($payment->id)
        ->and($log->event_type)->toBe('midtrans.notification')
        ->and($log->transaction_status)->toBe('settlement')
        ->and($log->signature_valid)->toBeTrue()
        ->and($log->error_message)->toBeNull()
        ->and($log->processed_at)->not->toBeNull();
});

test('invalid signature membuat payment_log signature_valid false', function (): void {
    $user = User::factory()->create(['role' => 'user']);
    $booking = phase2Booking($user, $this->service);
    $payment = phase2Payment($booking);

    $this->postJson(route('payments.midtrans.notification'), phase2Payload($payment, [
        'signature_key' => 'invalid-signature',
    ]))
        ->assertStatus(400)
        ->assertJson(['message' => 'Signature Midtrans tidak valid.']);

    $log = PaymentLog::first();

    expect($log)->not->toBeNull()
        ->and($log->payment_id)->toBe($payment->id)
        ->and($log->signature_valid)->toBeFalse()
        ->and($log->error_message)->toBe('signature invalid');
});

test('duplicate settlement tidak membuat ticket ganda', function (): void {
    $user = User::factory()->create(['role' => 'user']);
    $booking = phase2Booking($user, $this->service);
    $payment = phase2Payment($booking);
    $payload = phase2Payload($payment);

    $this->postJson(route('payments.midtrans.notification'), $payload)->assertOk();
    $this->postJson(route('payments.midtrans.notification'), $payload)->assertOk();

    expect($payment->fresh()->status)->toBe(Payment::STATUS_PAID)
        ->and($booking->fresh()->status)->toBe(Booking::STATUS_CONFIRMED)
        ->and(Ticket::where('booking_id', $booking->id)->count())->toBe(1)
        ->and(PaymentLog::where('payment_id', $payment->id)->count())->toBe(2);
});

test('expire webhook membuat booking dan payment expired jika masih pending', function (): void {
    $user = User::factory()->create(['role' => 'user']);
    $booking = phase2Booking($user, $this->service);
    $payment = phase2Payment($booking, ['status' => Payment::STATUS_PENDING]);

    $this->postJson(route('payments.midtrans.notification'), phase2Payload($payment, [
        'transaction_status' => 'expire',
    ]))
        ->assertOk();

    expect($payment->fresh()->status)->toBe(Payment::STATUS_EXPIRED)
        ->and($payment->fresh()->expired_at)->not->toBeNull()
        ->and($booking->fresh()->status)->toBe(Booking::STATUS_EXPIRED);
});

test('cancel deny failure webhook mengubah status sesuai', function (): void {
    $cases = [
        'cancel' => Payment::STATUS_CANCELLED,
        'deny' => Payment::STATUS_FAILED,
        'failure' => Payment::STATUS_FAILED,
    ];

    foreach ($cases as $transactionStatus => $expectedPaymentStatus) {
        $user = User::factory()->create(['role' => 'user']);
        $booking = phase2Booking($user, $this->service);
        $payment = phase2Payment($booking);

        $this->postJson(route('payments.midtrans.notification'), phase2Payload($payment, [
            'transaction_status' => $transactionStatus,
        ]))
            ->assertOk();

        expect($payment->fresh()->status)->toBe($expectedPaymentStatus)
            ->and($booking->fresh()->status)->toBe(Booking::STATUS_CANCELLED);
    }
});

test('paid capture webhook membuat payment_log tersimpan', function (): void {
    $user = User::factory()->create(['role' => 'user']);
    $booking = phase2Booking($user, $this->service);
    $payment = phase2Payment($booking);

    $this->postJson(route('payments.midtrans.notification'), phase2Payload($payment, [
        'transaction_status' => 'capture',
        'fraud_status' => 'accept',
    ]))
        ->assertOk();

    $log = PaymentLog::first();

    expect($payment->fresh()->status)->toBe(Payment::STATUS_PAID)
        ->and($log->transaction_status)->toBe('capture')
        ->and($log->fraud_status)->toBe('accept')
        ->and($log->signature_valid)->toBeTrue()
        ->and($log->processed_at)->not->toBeNull();
});

test('qr code tampil di halaman e-ticket setelah paid', function (): void {
    $user = User::factory()->create(['role' => 'user']);
    $booking = phase2Booking($user, $this->service);
    $payment = phase2Payment($booking);

    $this->postJson(route('payments.midtrans.notification'), phase2Payload($payment))
        ->assertOk();

    $ticket = $booking->fresh()->ticket;

    $this->actingAs($user)
        ->get(route('my-bookings.ticket', $booking))
        ->assertOk()
        ->assertSee('data-testid="ticket-qr"', false)
        ->assertSee('<svg', false)
        ->assertSee($ticket->ticket_code);
});
