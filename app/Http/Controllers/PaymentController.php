<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePaymentRequest;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function create(Booking $booking): View
    {
        Gate::authorize('uploadPayment', $booking);

        return view('payments.create', [
            'booking' => $booking->load('service'),
        ]);
    }

    public function store(StorePaymentRequest $request, Booking $booking): RedirectResponse
    {
        Gate::authorize('uploadPayment', $booking);

        $path = $request->file('proof')->store('payments', 'public');

        DB::transaction(function () use ($booking, $path): void {
            $booking->payment()->updateOrCreate([], [
                'booking_id' => $booking->id,
                'file_path' => $path,
                'status' => Payment::STATUS_PENDING,
                'uploaded_at' => now(),
            ]);

            $booking->update([
                'status' => Booking::STATUS_WAITING_VERIFICATION,
            ]);
        });

        return redirect()
            ->route('my-bookings.show', $booking)
            ->with('success', 'Bukti pembayaran berhasil diunggah dan menunggu verifikasi admin.');
    }

    public function notification(Request $request, MidtransService $midtrans): JsonResponse
    {
        try {
            $midtrans->handleNotification($request->all());

            return response()->json(['message' => 'Notification processed.']);
        } catch (\Throwable $exception) {
            Log::error('Gagal process webhook Midtrans.', [
                'order_id' => $request->input('order_id'),
                'transaction_status' => $request->input('transaction_status'),
                'exception' => $exception->getMessage(),
            ]);

            return response()->json(['message' => $exception->getMessage()], 400);
        }
    }
}
