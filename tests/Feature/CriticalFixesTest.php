<?php

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;

beforeEach(function (): void {
    $this->service = Service::create([
        'name' => 'Tiket Test',
        'slug' => 'tiket-test',
        'description' => 'Paket test',
        'price' => 10000,
        'pricing_type' => 'per_person',
        'is_active' => true,
    ]);
});

function makeCriticalBooking(User $user, Service $service, array $attributes = []): Booking
{
    return Booking::create(array_merge([
        'booking_code' => 'BK-TEST-'.str()->random(8),
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

function makeCriticalPayment(Booking $booking, array $attributes = []): Payment
{
    return $booking->payment()->create(array_merge([
        'order_id' => 'ORDER-'.$booking->booking_code,
        'gross_amount' => $booking->total_price,
        'status' => Payment::STATUS_UNPAID,
        'expired_at' => $booking->expires_at,
    ], $attributes));
}

function validMidtransSignature(array $payload): string
{
    return hash(
        'sha512',
        $payload['order_id'].$payload['status_code'].$payload['gross_amount'].config('midtrans.server_key')
    );
}

test('guest tidak bisa checkout', function (): void {
    $user = User::factory()->create(['role' => 'user']);
    $booking = makeCriticalBooking($user, $this->service);
    makeCriticalPayment($booking);

    $this->get(route('bookings.checkout', $booking))
        ->assertRedirect(route('login'));
});

test('user tidak bisa akses booking user lain', function (): void {
    $owner = User::factory()->create(['role' => 'user']);
    $otherUser = User::factory()->create(['role' => 'user']);
    $booking = makeCriticalBooking($owner, $this->service);
    makeCriticalPayment($booking);

    $this->actingAs($otherUser)
        ->get(route('my-bookings.show', $booking))
        ->assertForbidden();
});

test('webhook fake signature ditolak', function (): void {
    config(['midtrans.server_key' => 'test-server-key']);

    $user = User::factory()->create(['role' => 'user']);
    $booking = makeCriticalBooking($user, $this->service);
    $payment = makeCriticalPayment($booking);

    $this->postJson(route('payments.midtrans.notification'), [
        'order_id' => $payment->order_id,
        'status_code' => '200',
        'gross_amount' => '10000.00',
        'transaction_status' => 'settlement',
        'signature_key' => 'invalid-signature',
    ])
        ->assertStatus(400)
        ->assertJson(['message' => 'Signature Midtrans tidak valid.']);
});

test('paid webhook membuat payment paid booking confirmed dan ticket dibuat', function (): void {
    config(['midtrans.server_key' => 'test-server-key']);

    $user = User::factory()->create(['role' => 'user']);
    $booking = makeCriticalBooking($user, $this->service);
    $payment = makeCriticalPayment($booking);

    $payload = [
        'order_id' => $payment->order_id,
        'status_code' => '200',
        'gross_amount' => '10000.00',
        'transaction_status' => 'settlement',
        'payment_type' => 'bank_transfer',
    ];
    $payload['signature_key'] = validMidtransSignature($payload);

    $this->postJson(route('payments.midtrans.notification'), $payload)
        ->assertOk()
        ->assertJson(['message' => 'Notification processed.']);

    $booking->refresh();
    $payment->refresh();

    expect($payment->status)->toBe(Payment::STATUS_PAID)
        ->and($payment->paid_at)->not->toBeNull()
        ->and($booking->status)->toBe(Booking::STATUS_CONFIRMED)
        ->and($booking->ticket()->exists())->toBeTrue();
});

test('expired booking tidak bisa dibayar', function (): void {
    $user = User::factory()->create(['role' => 'user']);
    $booking = makeCriticalBooking($user, $this->service, [
        'expires_at' => now()->subMinute(),
    ]);
    makeCriticalPayment($booking, [
        'expired_at' => now()->subMinute(),
    ]);

    expect($booking->fresh()->canPay())->toBeFalse();

    $this->actingAs($user)
        ->post(route('bookings.pay', $booking))
        ->assertForbidden();
});

test('bookings expire command mengubah booking dan payment lama menjadi expired', function (): void {
    $user = User::factory()->create(['role' => 'user']);
    $booking = makeCriticalBooking($user, $this->service, [
        'expires_at' => now()->subMinute(),
    ]);
    $payment = makeCriticalPayment($booking, [
        'status' => Payment::STATUS_PENDING,
        'expired_at' => now()->addHour(),
    ]);

    $this->artisan('bookings:expire')
        ->assertSuccessful();

    expect($booking->fresh()->status)->toBe(Booking::STATUS_EXPIRED)
        ->and($payment->fresh()->status)->toBe(Payment::STATUS_EXPIRED)
        ->and($payment->fresh()->expired_at)->not->toBeNull();
});
