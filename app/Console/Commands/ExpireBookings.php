<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExpireBookings extends Command
{
    protected $signature = 'bookings:expire';

    protected $description = 'Expire waiting payment bookings past expires_at and their unpaid or pending payments.';

    public function handle(): int
    {
        $now = now();
        $expiredBookings = 0;
        $expiredPayments = 0;

        Booking::query()
            ->where('status', Booking::STATUS_WAITING_PAYMENT)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', $now)
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($bookings) use ($now, &$expiredBookings, &$expiredPayments): void {
                $bookingIds = $bookings->pluck('id');

                DB::transaction(function () use ($bookingIds, $now, &$expiredBookings, &$expiredPayments): void {
                    $expiredPayments += Payment::query()
                        ->whereIn('booking_id', $bookingIds)
                        ->whereIn('status', [Payment::STATUS_UNPAID, Payment::STATUS_PENDING])
                        ->update([
                            'status' => Payment::STATUS_EXPIRED,
                            'expired_at' => $now,
                        ]);

                    $expiredBookings += Booking::query()
                        ->whereIn('id', $bookingIds)
                        ->where('status', Booking::STATUS_WAITING_PAYMENT)
                        ->update([
                            'status' => Booking::STATUS_EXPIRED,
                        ]);
                });
            });

        $this->info("Expired {$expiredBookings} booking(s) and {$expiredPayments} payment(s).");

        return self::SUCCESS;
    }
}
