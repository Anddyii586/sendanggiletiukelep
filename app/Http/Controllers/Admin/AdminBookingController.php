<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Ticket;
use App\Services\AdminAuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            'booking' => $booking->load(['user', 'service', 'payment', 'ticket', 'review.user', 'cancelledBy']),
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

    public function reject(Request $request, Booking $booking, AdminAuditLogService $auditLog): RedirectResponse
    {
        return $this->cancel($request, $booking, $auditLog);
    }

    public function complete(Request $request, Booking $booking, AdminAuditLogService $auditLog): RedirectResponse
    {
        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            return back()->with('error', 'Hanya booking confirmed yang dapat ditandai selesai.');
        }

        $previousStatus = $booking->status;

        DB::transaction(function () use ($booking, $request, $auditLog, $previousStatus): void {
            $booking->update([
                'status' => Booking::STATUS_COMPLETED,
            ]);

            $booking->ticket?->update([
                'status' => Ticket::STATUS_USED,
                'checked_in_at' => now(),
            ]);

            $auditLog->log(
                $request->user(),
                'booking.completed',
                $booking,
                "Booking {$booking->booking_code} ditandai completed.",
                [
                    'previous_status' => $previousStatus,
                    'new_status' => Booking::STATUS_COMPLETED,
                    'payment_status' => $booking->payment?->status,
                ]
            );
        });

        return back()->with('success', 'Booking ditandai completed.');
    }

    public function cancel(Request $request, Booking $booking, AdminAuditLogService $auditLog): RedirectResponse
    {
        if ($booking->status === Booking::STATUS_COMPLETED) {
            return back()->with('error', 'Booking completed tidak dapat dibatalkan.');
        }

        $validated = $request->validate([
            'cancelled_reason' => ['required', 'string', 'min:5', 'max:2000'],
        ]);
        $previousStatus = $booking->status;
        $previousPaymentStatus = $booking->payment?->status;

        DB::transaction(function () use ($booking, $request, $auditLog, $validated, $previousStatus, $previousPaymentStatus): void {
            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancelled_reason' => $validated['cancelled_reason'],
                'cancelled_at' => now(),
                'cancelled_by' => $request->user()->id,
            ]);

            if ($booking->payment && $booking->payment->status !== Payment::STATUS_PAID) {
                $booking->payment->update(['status' => Payment::STATUS_CANCELLED]);
            }

            $booking->ticket?->update(['status' => Ticket::STATUS_CANCELLED]);

            $auditLog->log(
                $request->user(),
                'booking.cancelled',
                $booking,
                "Booking {$booking->booking_code} dibatalkan.",
                [
                    'previous_status' => $previousStatus,
                    'new_status' => Booking::STATUS_CANCELLED,
                    'previous_payment_status' => $previousPaymentStatus,
                    'new_payment_status' => $booking->payment?->fresh()?->status,
                    'reason' => $validated['cancelled_reason'],
                ]
            );
        });

        return back()->with('success', 'Booking dibatalkan.');
    }
}
