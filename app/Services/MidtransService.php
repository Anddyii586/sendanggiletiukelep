<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\MidtransException;
use Midtrans\Snap;

class MidtransService
{
    public function __construct()
    {
        Config::$serverKey = (string) config('midtrans.server_key');
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized = (bool) config('midtrans.is_sanitized');
        Config::$is3ds = (bool) config('midtrans.is_3ds');
    }

    public function createSnapToken(Booking $booking): string
    {
        $booking->loadMissing(['service', 'user', 'payment']);

        if (! config('midtrans.server_key')) {
            throw new \RuntimeException('MIDTRANS_SERVER_KEY belum diisi.');
        }

        $payment = $booking->payment ?: $booking->payment()->create([
            'order_id' => $this->makeOrderId($booking),
            'gross_amount' => $booking->total_price,
            'status' => Payment::STATUS_UNPAID,
        ]);

        if (! $payment->order_id) {
            $payment->update(['order_id' => $this->makeOrderId($booking)]);
        }

        if ($payment->snap_token && in_array($payment->status, [Payment::STATUS_UNPAID, Payment::STATUS_PENDING], true)) {
            return $payment->snap_token;
        }

        return $this->createTransaction($booking, $payment);
    }

    private function createTransaction(Booking $booking, Payment $payment, bool $hasRetried = false): string
    {
        $params = [
            'transaction_details' => [
                'order_id' => $payment->order_id,
                'gross_amount' => (int) round((float) $booking->total_price),
            ],
            'customer_details' => [
                'first_name' => $booking->contact_name ?: $booking->user->name,
                'email' => $booking->contact_email ?: $booking->user->email,
                'phone' => $booking->contact_phone ?: $booking->user->phone,
            ],
            'item_details' => [
                [
                    'id' => (string) $booking->service->id,
                    'price' => (int) round((float) $booking->service->price),
                    'quantity' => $booking->service->pricing_type === 'per_trip' ? 1 : $booking->participant_count,
                    'name' => Str::limit($booking->service->name, 45, ''),
                ],
            ],
            'callbacks' => [
                'finish' => route('my-bookings.show', $booking),
            ],
        ];

        if ((float) $booking->service_fee > 0) {
            $params['item_details'][] = [
                'id' => 'service-fee',
                'price' => (int) round((float) $booking->service_fee),
                'quantity' => 1,
                'name' => 'Biaya layanan',
            ];
        }

        try {
            $transaction = Snap::createTransaction($params);
        } catch (MidtransException $exception) {
            if (! $hasRetried && str_contains(strtolower($exception->getMessage()), 'order_id')) {
                $payment->update([
                    'order_id' => $this->makeOrderId($booking).'-'.strtoupper(Str::random(4)),
                    'snap_token' => null,
                    'snap_redirect_url' => null,
                    'status' => Payment::STATUS_UNPAID,
                ]);

                return $this->createTransaction($booking, $payment->refresh(), true);
            }

            throw $exception;
        }

        $payment->update([
            'snap_token' => $transaction->token,
            'snap_redirect_url' => $transaction->redirect_url ?? null,
            'gross_amount' => $booking->total_price,
            'status' => in_array($payment->status, [Payment::STATUS_UNPAID, Payment::STATUS_PENDING], true)
                ? Payment::STATUS_PENDING
                : $payment->status,
        ]);

        return $transaction->token;
    }

    public function handleNotification(array $payload): void
    {
        if (! $this->validateSignature($payload)) {
            throw new \RuntimeException('Signature Midtrans tidak valid.');
        }

        DB::transaction(function () use ($payload): void {
            $payment = Payment::query()
                ->where('order_id', $payload['order_id'] ?? null)
                ->lockForUpdate()
                ->firstOrFail();

            $booking = $payment->booking()->lockForUpdate()->firstOrFail();
            $transactionStatus = $payload['transaction_status'] ?? null;
            $fraudStatus = $payload['fraud_status'] ?? null;

            $paymentUpdates = [
                'payment_type' => $payload['payment_type'] ?? $payment->payment_type,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'gross_amount' => $payload['gross_amount'] ?? $payment->gross_amount,
                'raw_response' => $payload,
            ];

            if ($this->isPaid($transactionStatus, $fraudStatus)) {
                $paymentUpdates['status'] = Payment::STATUS_PAID;
                $paymentUpdates['paid_at'] = $payment->paid_at ?: now();
                $booking->update(['status' => Booking::STATUS_CONFIRMED]);
                $this->ensureTicket($booking);
            } elseif ($transactionStatus === 'pending') {
                $paymentUpdates['status'] = Payment::STATUS_PENDING;
                $booking->update(['status' => Booking::STATUS_WAITING_PAYMENT]);
            } elseif ($transactionStatus === 'expire') {
                $paymentUpdates['status'] = Payment::STATUS_EXPIRED;
                $paymentUpdates['expired_at'] = now();
                $booking->update(['status' => Booking::STATUS_EXPIRED]);
                $booking->ticket?->update(['status' => Ticket::STATUS_EXPIRED]);
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'failure'], true)) {
                $paymentUpdates['status'] = $transactionStatus === 'cancel'
                    ? Payment::STATUS_CANCELLED
                    : Payment::STATUS_FAILED;
                $booking->update(['status' => Booking::STATUS_CANCELLED]);
                $booking->ticket?->update(['status' => Ticket::STATUS_CANCELLED]);
            }

            $payment->update($paymentUpdates);
        });
    }

    public function validateSignature(array $payload): bool
    {
        foreach (['order_id', 'status_code', 'gross_amount', 'signature_key'] as $key) {
            if (! array_key_exists($key, $payload)) {
                return false;
            }
        }

        $signature = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].config('midtrans.server_key')
        );

        return hash_equals($signature, (string) $payload['signature_key']);
    }

    public function ensureTicket(Booking $booking): Ticket
    {
        $booking->loadMissing('ticket');

        if ($booking->ticket) {
            return $booking->ticket;
        }

        return $booking->ticket()->create([
            'ticket_code' => 'TKT-'.$booking->booking_code,
            'status' => Ticket::STATUS_ACTIVE,
        ]);
    }

    private function makeOrderId(Booking $booking): string
    {
        return 'ORDER-'.$booking->booking_code;
    }

    private function isPaid(?string $transactionStatus, ?string $fraudStatus): bool
    {
        if ($transactionStatus === 'settlement') {
            return true;
        }

        return $transactionStatus === 'capture'
            && in_array($fraudStatus, [null, 'accept'], true);
    }
}
