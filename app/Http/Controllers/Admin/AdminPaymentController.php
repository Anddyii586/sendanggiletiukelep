<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPaymentController extends Controller
{
    public function index(Request $request): View
    {
        $statuses = [
            Payment::STATUS_UNPAID,
            Payment::STATUS_PENDING,
            Payment::STATUS_PAID,
            Payment::STATUS_FAILED,
            Payment::STATUS_EXPIRED,
            Payment::STATUS_CANCELLED,
        ];
        $dateColumns = [
            'created_at' => 'Created At',
            'paid_at' => 'Paid At',
        ];
        $dateColumn = array_key_exists($request->input('date_column'), $dateColumns)
            ? $request->input('date_column')
            : 'created_at';
        $search = trim((string) $request->input('search'));

        $payments = Payment::query()
            ->with(['booking.user', 'booking.service'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $like = "%{$search}%";

                    $query->where('order_id', 'like', $like)
                        ->orWhereHas('booking', function ($bookingQuery) use ($like): void {
                            $bookingQuery->where('booking_code', 'like', $like)
                                ->orWhere('contact_name', 'like', $like)
                                ->orWhere('contact_email', 'like', $like)
                                ->orWhere('contact_phone', 'like', $like)
                                ->orWhereHas('user', function ($userQuery) use ($like): void {
                                    $userQuery->where('name', 'like', $like)
                                        ->orWhere('email', 'like', $like)
                                        ->orWhere('phone', 'like', $like);
                                });
                        });
                });
            })
            ->when(
                in_array($request->input('status'), $statuses, true),
                fn ($query) => $query->where('status', $request->input('status'))
            )
            ->when(
                $request->filled('payment_type'),
                fn ($query) => $query->where('payment_type', $request->input('payment_type'))
            )
            ->when(
                $request->filled('date_from'),
                fn ($query) => $query->whereDate($dateColumn, '>=', $request->input('date_from'))
            )
            ->when(
                $request->filled('date_to'),
                fn ($query) => $query->whereDate($dateColumn, '<=', $request->input('date_to'))
            )
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.payments.index', [
            'payments' => $payments,
            'statuses' => $statuses,
            'paymentTypes' => Payment::query()
                ->whereNotNull('payment_type')
                ->where('payment_type', '!=', '')
                ->distinct()
                ->orderBy('payment_type')
                ->pluck('payment_type'),
            'dateColumns' => $dateColumns,
            'dateColumn' => $dateColumn,
        ]);
    }
}
