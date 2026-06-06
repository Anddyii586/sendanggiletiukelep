<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AdminBookingController extends Controller
{
    public function index(\Illuminate\Http\Request $request): View
    {
        $bookings = Booking::query()
            ->with(['user', 'service', 'payment', 'ticket'])
            ->when(
                $request->filled('status') && in_array($request->input('status'), Booking::STATUSES, true),
                fn ($query) => $query->where('status', $request->input('status'))
            )
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('visit_date', '>=', $request->input('date_from')))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('visit_date', '<=', $request->input('date_to')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'statuses' => Booking::STATUSES,
        ]);
    }

    public function show(Booking $booking): View
    {
        return view('admin.bookings.show', [
            'booking' => $booking->load(['user', 'service', 'payment', 'ticket', 'review.user']),
        ]);
    }

    public function approve(Booking $booking): RedirectResponse
    {
        if ($booking->payment?->status === Payment::STATUS_PAID) {
            $booking->update(['status' => Booking::STATUS_CONFIRMED]);

            return back()->with('success', 'Booking sudah paid dari Midtrans dan dikonfirmasi.');
        }

        return back()->with('error', 'Approve manual disembunyikan dari flow utama. Gunakan pembayaran Midtrans.');
    }

    public function reject(Booking $booking): RedirectResponse
    {
        return $this->cancel($booking);
    }

    public function complete(Booking $booking): RedirectResponse
    {
        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            return back()->with('error', 'Hanya booking confirmed yang dapat ditandai selesai.');
        }

        DB::transaction(function () use ($booking): void {
            $booking->update([
                'status' => Booking::STATUS_COMPLETED,
            ]);

            $booking->ticket?->update([
                'status' => Ticket::STATUS_USED,
                'checked_in_at' => now(),
            ]);
        });

        return back()->with('success', 'Booking ditandai completed.');
    }

    public function cancel(Booking $booking): RedirectResponse
    {
        if ($booking->status === Booking::STATUS_COMPLETED) {
            return back()->with('error', 'Booking completed tidak dapat dibatalkan.');
        }

        DB::transaction(function () use ($booking): void {
            $booking->update(['status' => Booking::STATUS_CANCELLED]);

            if ($booking->payment && $booking->payment->status !== Payment::STATUS_PAID) {
                $booking->payment->update(['status' => Payment::STATUS_CANCELLED]);
            }

            $booking->ticket?->update(['status' => Ticket::STATUS_CANCELLED]);
        });

        return back()->with('success', 'Booking dibatalkan.');
    }
}
