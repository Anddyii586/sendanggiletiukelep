<?php

use App\Models\AdminAuditLog;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Review;
use App\Models\Service;
use App\Models\SiteSetting;
use App\Models\User;

function phase3User(string $role = 'user', array $attributes = []): User
{
    return User::factory()->create(array_merge([
        'role' => $role,
        'phone' => '081234567890',
    ], $attributes));
}

function phase3Service(array $attributes = []): Service
{
    $name = $attributes['name'] ?? 'Phase 3 Package';

    return Service::create(array_merge([
        'name' => $name,
        'slug' => str()->slug($name).'-'.strtolower(str()->random(6)),
        'description' => 'Phase 3 test package',
        'price' => 150000,
        'pricing_type' => 'per_person',
        'is_active' => true,
    ], $attributes));
}

function phase3Booking(User $user, Service $service, array $attributes = []): Booking
{
    return Booking::create(array_merge([
        'booking_code' => 'BK-P3-'.strtoupper(str()->random(8)),
        'user_id' => $user->id,
        'service_id' => $service->id,
        'visit_date' => now()->addDays(3)->toDateString(),
        'participant_count' => 2,
        'contact_name' => $user->name,
        'contact_phone' => $user->phone,
        'contact_email' => $user->email,
        'subtotal' => 300000,
        'service_fee' => 0,
        'total_price' => 300000,
        'status' => Booking::STATUS_WAITING_PAYMENT,
        'expires_at' => now()->addHour(),
    ], $attributes));
}

function phase3Payment(Booking $booking, array $attributes = []): Payment
{
    return $booking->payment()->create(array_merge([
        'order_id' => 'ORDER-'.$booking->booking_code,
        'payment_type' => 'bank_transfer',
        'transaction_status' => 'pending',
        'fraud_status' => 'accept',
        'gross_amount' => $booking->total_price,
        'status' => Payment::STATUS_UNPAID,
        'expired_at' => $booking->expires_at,
    ], $attributes));
}

test('user biasa tidak bisa akses admin payment monitoring', function (): void {
    $this->actingAs(phase3User())
        ->get(route('admin.payments.index'))
        ->assertRedirect(route('my-bookings.index'));
});

test('admin bisa akses admin payment monitoring', function (): void {
    $admin = phase3User('admin');
    $user = phase3User();
    $service = phase3Service();
    $booking = phase3Booking($user, $service);
    $payment = phase3Payment($booking);

    $this->actingAs($admin)
        ->get(route('admin.payments.index'))
        ->assertOk()
        ->assertSee($payment->order_id)
        ->assertSee($booking->booking_code);
});

test('admin bisa filter payments berdasarkan status', function (): void {
    $admin = phase3User('admin');
    $user = phase3User();
    $service = phase3Service();
    $paidBooking = phase3Booking($user, $service);
    $pendingBooking = phase3Booking($user, $service);
    $paidPayment = phase3Payment($paidBooking, [
        'order_id' => 'ORDER-PAID-P3',
        'status' => Payment::STATUS_PAID,
        'paid_at' => now(),
    ]);
    $pendingPayment = phase3Payment($pendingBooking, [
        'order_id' => 'ORDER-PENDING-P3',
        'status' => Payment::STATUS_PENDING,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.payments.index', ['status' => Payment::STATUS_PAID]))
        ->assertOk()
        ->assertSee($paidPayment->order_id)
        ->assertDontSee($pendingPayment->order_id);
});

test('admin bisa akses transaction report', function (): void {
    $admin = phase3User('admin');
    $user = phase3User();
    $service = phase3Service();
    $booking = phase3Booking($user, $service);
    phase3Payment($booking, ['status' => Payment::STATUS_PAID, 'paid_at' => now()]);

    $this->actingAs($admin)
        ->get(route('admin.reports.transactions'))
        ->assertOk()
        ->assertSee('Transaction Report')
        ->assertSee($booking->booking_code);
});

test('admin bisa export CSV transaksi dengan header benar', function (): void {
    $admin = phase3User('admin');
    $user = phase3User();
    $service = phase3Service();
    $booking = phase3Booking($user, $service);
    phase3Payment($booking, ['status' => Payment::STATUS_PAID, 'paid_at' => now()]);

    $response = $this->actingAs($admin)
        ->get(route('admin.reports.transactions.export'));

    $response->assertOk();

    $content = $response->streamedContent();
    $header = strtok(substr($content, 3), "\n");

    expect(str_starts_with($content, "\xEF\xBB\xBF"))->toBeTrue()
        ->and($header)->toContain(';')
        ->and(str_getcsv($header, ';'))->toBe([
        'Booking Code',
        'Order ID',
        'Customer Name',
        'Customer Email',
        'Customer Phone',
        'Package',
        'Visit Date',
        'Participant Count',
        'Booking Status',
        'Payment Status',
        'Payment Type',
        'Gross Amount',
        'Paid At',
        'Created At',
    ]);
});

test('user biasa tidak bisa akses admin users', function (): void {
    $this->actingAs(phase3User())
        ->get(route('admin.users.index'))
        ->assertRedirect(route('my-bookings.index'));
});

test('admin bisa akses admin users', function (): void {
    $admin = phase3User('admin');
    $user = phase3User('user', ['email' => 'phase3-user@example.com']);

    $this->actingAs($admin)
        ->get(route('admin.users.index'))
        ->assertOk()
        ->assertSee($user->email);
});

test('admin bisa lihat detail user', function (): void {
    $admin = phase3User('admin');
    $user = phase3User();
    $service = phase3Service();
    $booking = phase3Booking($user, $service);
    $payment = phase3Payment($booking, ['status' => Payment::STATUS_PAID, 'paid_at' => now()]);
    Review::create([
        'booking_id' => $booking->id,
        'user_id' => $user->id,
        'rating' => 5,
        'comment' => 'Great trip',
        'is_visible' => true,
    ]);

    $this->actingAs($admin)
        ->get(route('admin.users.show', $user))
        ->assertOk()
        ->assertSee($booking->booking_code)
        ->assertSee($payment->order_id)
        ->assertSee('Riwayat Review');
});

test('cancel booking membuat admin audit log', function (): void {
    $admin = phase3User('admin');
    $user = phase3User();
    $service = phase3Service();
    $booking = phase3Booking($user, $service);
    phase3Payment($booking, ['status' => Payment::STATUS_PENDING]);

    $this->actingAs($admin)
        ->patch(route('admin.bookings.cancel', $booking), [
            'cancelled_reason' => 'Customer requested schedule change.',
        ])
        ->assertSessionHasNoErrors()
        ->assertRedirect();

    expect(AdminAuditLog::where('action', 'booking.cancelled')->where('admin_id', $admin->id)->exists())->toBeTrue();
});

test('complete booking membuat admin audit log', function (): void {
    $admin = phase3User('admin');
    $user = phase3User();
    $service = phase3Service();
    $booking = phase3Booking($user, $service, ['status' => Booking::STATUS_CONFIRMED]);
    phase3Payment($booking, ['status' => Payment::STATUS_PAID, 'paid_at' => now()]);

    $this->actingAs($admin)
        ->patch(route('admin.bookings.complete', $booking))
        ->assertRedirect();

    expect(AdminAuditLog::where('action', 'booking.completed')->where('admin_id', $admin->id)->exists())->toBeTrue();
});

test('review visibility toggle membuat admin audit log', function (): void {
    $admin = phase3User('admin');
    $user = phase3User();
    $service = phase3Service();
    $booking = phase3Booking($user, $service, ['status' => Booking::STATUS_COMPLETED]);
    $review = Review::create([
        'booking_id' => $booking->id,
        'user_id' => $user->id,
        'rating' => 4,
        'comment' => 'Nice route',
        'is_visible' => true,
    ]);

    $this->actingAs($admin)
        ->patch(route('admin.reviews.visibility', $review))
        ->assertRedirect();

    expect($review->fresh()->is_visible)->toBeFalse()
        ->and(AdminAuditLog::where('action', 'review.visibility_toggled')->where('admin_id', $admin->id)->exists())->toBeTrue();
});

test('update site settings membuat admin audit log', function (): void {
    $admin = phase3User('admin');

    SiteSetting::create(['key' => 'destination_name', 'value' => 'Old Name']);

    $this->actingAs($admin)
        ->put(route('admin.site-settings.update'), [
            'destination_name' => 'New Destination Name',
        ])
        ->assertRedirect();

    expect(SiteSetting::where('key', 'destination_name')->value('value'))->toBe('New Destination Name')
        ->and(AdminAuditLog::where('action', 'site_settings.updated')->where('admin_id', $admin->id)->exists())->toBeTrue();
});

test('cancel booking wajib reason', function (): void {
    $admin = phase3User('admin');
    $user = phase3User();
    $service = phase3Service();
    $booking = phase3Booking($user, $service);
    phase3Payment($booking);

    $this->actingAs($admin)
        ->patch(route('admin.bookings.cancel', $booking))
        ->assertSessionHasErrors('cancelled_reason');

    expect($booking->fresh()->status)->toBe(Booking::STATUS_WAITING_PAYMENT);
});

test('cancel booking menyimpan cancelled reason at dan by', function (): void {
    $admin = phase3User('admin');
    $user = phase3User();
    $service = phase3Service();
    $booking = phase3Booking($user, $service);
    $payment = phase3Payment($booking, ['status' => Payment::STATUS_PENDING]);

    $this->actingAs($admin)
        ->patch(route('admin.bookings.cancel', $booking), [
            'cancelled_reason' => 'Weather conditions are unsafe.',
        ])
        ->assertSessionHasNoErrors();

    $booking->refresh();
    $payment->refresh();

    expect($booking->status)->toBe(Booking::STATUS_CANCELLED)
        ->and($booking->cancelled_reason)->toBe('Weather conditions are unsafe.')
        ->and($booking->cancelled_at)->not->toBeNull()
        ->and($booking->cancelled_by)->toBe($admin->id)
        ->and($payment->status)->toBe(Payment::STATUS_CANCELLED);
});

test('admin tidak bisa cancel booking completed', function (): void {
    $admin = phase3User('admin');
    $user = phase3User();
    $service = phase3Service();
    $booking = phase3Booking($user, $service, ['status' => Booking::STATUS_COMPLETED]);
    phase3Payment($booking, ['status' => Payment::STATUS_PAID, 'paid_at' => now()]);

    $this->actingAs($admin)
        ->patch(route('admin.bookings.cancel', $booking), [
            'cancelled_reason' => 'Trying to cancel completed booking.',
        ])
        ->assertSessionHas('error');

    expect($booking->fresh()->status)->toBe(Booking::STATUS_COMPLETED)
        ->and(AdminAuditLog::where('action', 'booking.cancelled')->exists())->toBeFalse();
});
