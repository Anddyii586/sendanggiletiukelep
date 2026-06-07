<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\PaymentLog;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
            Log::error('Gagal create Snap token: MIDTRANS_SERVER_KEY belum diisi.', [
                'booking_id' => $booking->id,
                'booking_code' => $booking->booking_code,
            ]);

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
                Log::warning('Gagal create Snap token karena order_id, mencoba ulang.', [
                    'booking_id' => $booking->id,
                    'payment_id' => $payment->id,
                    'order_id' => $payment->order_id,
                    'exception' => $exception->getMessage(),
                ]);

                $payment->update([
                    'order_id' => $this->makeOrderId($booking).'-'.strtoupper(Str::random(4)),
                    'snap_token' => null,
                    'snap_redirect_url' => null,
                    'status' => Payment::STATUS_UNPAID,
                ]);

                return $this->createTransaction($booking, $payment->refresh(), true);
            }

            Log::error('Gagal create Snap token Midtrans.', [
                'booking_id' => $booking->id,
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'exception' => $exception->getMessage(),
            ]);

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
        $signatureValid = $this->validateSignature($payload);

        if (! $signatureValid) {
            $payment = Payment::query()
                ->where('order_id', $payload['order_id'] ?? null)
                ->first();

            $paymentLog = $this->createPaymentLog($payload, $payment, false, 'signature invalid');

            Log::warning('Signature Midtrans tidak valid.', $this->logContext($payload, [
                'payment_id' => $payment?->id,
                'payment_log_id' => $paymentLog->id,
            ]));

            throw new \RuntimeException('Signature Midtrans tidak valid.');
        }

        $responseException = null;

        DB::transaction(function () use ($payload, &$responseException): void {
            $transactionStatus = $payload['transaction_status'] ?? null;
            $fraudStatus = $payload['fraud_status'] ?? null;

            $payment = Payment::query()
                ->where('order_id', $payload['order_id'] ?? null)
                ->lockForUpdate()
                ->first();

            $paymentLog = $this->createPaymentLog($payload, $payment, true);

            if (! $payment) {
                $this->markPaymentLogProcessed($paymentLog, 'payment not found');

                Log::warning('Order ID Midtrans tidak ditemukan.', $this->logContext($payload, [
                    'payment_log_id' => $paymentLog->id,
                ]));

                $responseException = new \RuntimeException('Payment tidak ditemukan.');

                return;
            }

            $booking = $payment->booking()->lockForUpdate()->first();

            if (! $booking) {
                $this->markPaymentLogProcessed($paymentLog, 'booking not found');

                Log::error('Booking untuk payment Midtrans tidak ditemukan.', $this->logContext($payload, [
                    'payment_id' => $payment->id,
                    'payment_log_id' => $paymentLog->id,
                ]));

                $responseException = new \RuntimeException('Booking tidak ditemukan.');

                return;
            }

            if (! $this->grossAmountMatches($payload['gross_amount'] ?? null, $payment, $booking)) {
                $this->markPaymentLogProcessed($paymentLog, 'gross_amount mismatch');

                Log::warning('Gross amount Midtrans tidak cocok.', $this->logContext($payload, [
                    'payment_id' => $payment->id,
                    'payment_log_id' => $paymentLog->id,
                    'payment_gross_amount' => (string) $payment->gross_amount,
                    'booking_total_price' => (string) $booking->total_price,
                ]));

                $responseException = new \RuntimeException('gross_amount mismatch');

                return;
            }

            $paymentUpdates = [
                'payment_type' => $payload['payment_type'] ?? $payment->payment_type,
                'transaction_status' => $transactionStatus,
                'fraud_status' => $fraudStatus,
                'gross_amount' => $this->normalizeAmount($payload['gross_amount'] ?? $payment->gross_amount),
                'raw_response' => $payload,
            ];

            if ($this->isPaid($transactionStatus, $fraudStatus)) {
                $paymentUpdates['status'] = Payment::STATUS_PAID;
                $paymentUpdates['paid_at'] = $payment->paid_at ?: now();

                if ($booking->status !== Booking::STATUS_COMPLETED) {
                    $booking->update(['status' => Booking::STATUS_CONFIRMED]);
                }

                $this->ensureTicket($booking);
            } elseif ($transactionStatus === 'pending') {
                if ($this->paymentIsOpen($payment)) {
                    $paymentUpdates['status'] = Payment::STATUS_PENDING;

                    if ($booking->status === Booking::STATUS_WAITING_PAYMENT) {
                        $booking->update(['status' => Booking::STATUS_WAITING_PAYMENT]);
                    }
                } else {
                    Log::info('Webhook pending Midtrans diabaikan karena payment sudah final.', $this->logContext($payload, [
                        'payment_id' => $payment->id,
                        'payment_status' => $payment->status,
                    ]));
                }
            } elseif ($transactionStatus === 'expire') {
                if ($this->paymentIsOpen($payment)) {
                    $paymentUpdates['status'] = Payment::STATUS_EXPIRED;
                    $paymentUpdates['expired_at'] = now();

                    if ($booking->status === Booking::STATUS_WAITING_PAYMENT) {
                        $booking->update(['status' => Booking::STATUS_EXPIRED]);
                    }

                    $booking->ticket?->update(['status' => Ticket::STATUS_EXPIRED]);
                } else {
                    Log::info('Webhook expire Midtrans diabaikan karena payment sudah final.', $this->logContext($payload, [
                        'payment_id' => $payment->id,
                        'payment_status' => $payment->status,
                    ]));
                }
            } elseif (in_array($transactionStatus, ['cancel', 'deny', 'failure'], true)) {
                if ($this->paymentIsOpen($payment)) {
                    $paymentUpdates['status'] = $transactionStatus === 'cancel'
                        ? Payment::STATUS_CANCELLED
                        : Payment::STATUS_FAILED;

                    if (! in_array($booking->status, [Booking::STATUS_CONFIRMED, Booking::STATUS_COMPLETED], true)) {
                        $booking->update(['status' => Booking::STATUS_CANCELLED]);
                    }

                    $booking->ticket?->update(['status' => Ticket::STATUS_CANCELLED]);
                } else {
                    Log::info('Webhook gagal/cancel Midtrans diabaikan karena payment sudah final.', $this->logContext($payload, [
                        'payment_id' => $payment->id,
                        'payment_status' => $payment->status,
                    ]));
                }
            } else {
                $this->markPaymentLogProcessed($paymentLog, 'unexpected transaction_status');

                Log::warning('Transaction status Midtrans tidak dikenali.', $this->logContext($payload, [
                    'payment_id' => $payment->id,
                    'payment_log_id' => $paymentLog->id,
                ]));
            }

            $payment->update($paymentUpdates);

            if (! $paymentLog->processed_at) {
                $this->markPaymentLogProcessed($paymentLog);
            }
        });

        if ($responseException) {
            throw $responseException;
        }
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
        return $booking->ticket()->firstOrCreate([], [
            'ticket_code' => 'TKT-'.$booking->booking_code,
            'status' => Ticket::STATUS_ACTIVE,
        ]);
    }

    private function createPaymentLog(
        array $payload,
        ?Payment $payment,
        bool $signatureValid,
        ?string $errorMessage = null
    ): PaymentLog {
        return PaymentLog::create([
            'payment_id' => $payment?->id,
            'order_id' => $payload['order_id'] ?? $payment?->order_id,
            'event_type' => 'midtrans.notification',
            'transaction_status' => $payload['transaction_status'] ?? null,
            'fraud_status' => $payload['fraud_status'] ?? null,
            'gross_amount' => $this->normalizeAmount($payload['gross_amount'] ?? null),
            'signature_valid' => $signatureValid,
            'payload' => $payload,
            'error_message' => $errorMessage,
            'processed_at' => $errorMessage ? now() : null,
        ]);
    }

    private function markPaymentLogProcessed(PaymentLog $paymentLog, ?string $errorMessage = null): void
    {
        $paymentLog->update([
            'error_message' => $errorMessage,
            'processed_at' => now(),
        ]);
    }

    private function grossAmountMatches(mixed $grossAmount, Payment $payment, Booking $booking): bool
    {
        $incomingAmount = $this->amountToCents($grossAmount);

        if ($incomingAmount === null) {
            return false;
        }

        return $incomingAmount === $this->amountToCents($payment->gross_amount)
            || $incomingAmount === $this->amountToCents($booking->total_price);
    }

    private function normalizeAmount(mixed $amount): ?string
    {
        $cents = $this->amountToCents($amount);

        if ($cents === null) {
            return null;
        }

        $sign = $cents < 0 ? '-' : '';
        $absoluteCents = abs($cents);

        return $sign.intdiv($absoluteCents, 100).'.'.str_pad((string) ($absoluteCents % 100), 2, '0', STR_PAD_LEFT);
    }

    private function amountToCents(mixed $amount): ?int
    {
        if ($amount === null || $amount === '') {
            return null;
        }

        $amount = trim((string) $amount);

        if (! preg_match('/^-?\d+(?:\.\d+)?$/', $amount)) {
            return null;
        }

        $negative = str_starts_with($amount, '-');
        $amount = ltrim($amount, '-');
        [$whole, $fraction] = array_pad(explode('.', $amount, 2), 2, '');

        if (strlen($fraction) > 2 && trim(substr($fraction, 2), '0') !== '') {
            return null;
        }

        $whole = ltrim($whole, '0') ?: '0';
        $fraction = str_pad(substr($fraction, 0, 2), 2, '0', STR_PAD_RIGHT);
        $cents = ((int) $whole * 100) + (int) $fraction;

        return $negative ? -$cents : $cents;
    }

    private function paymentIsOpen(Payment $payment): bool
    {
        return in_array($payment->status, [Payment::STATUS_UNPAID, Payment::STATUS_PENDING], true);
    }

    private function logContext(array $payload, array $extra = []): array
    {
        return array_filter(array_merge([
            'order_id' => $payload['order_id'] ?? null,
            'transaction_status' => $payload['transaction_status'] ?? null,
            'fraud_status' => $payload['fraud_status'] ?? null,
            'gross_amount' => $payload['gross_amount'] ?? null,
        ], $extra), static fn ($value): bool => $value !== null);
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
