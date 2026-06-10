<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminReportController extends Controller
{
    private const CSV_HEADERS = [
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
    ];

    public function transactions(Request $request): View
    {
        $baseQuery = $this->transactionQuery($request);

        return view('admin.reports.transactions', [
            'bookings' => (clone $baseQuery)
                ->with(['user', 'service', 'payment'])
                ->latest('bookings.created_at')
                ->paginate(15)
                ->withQueryString(),
            'summary' => $this->summary($baseQuery),
            'paymentStatuses' => $this->paymentStatuses(),
            'bookingStatuses' => Booking::STATUSES,
        ]);
    }

    public function exportTransactions(Request $request): StreamedResponse
    {
        $bookings = (clone $this->transactionQuery($request))
            ->with(['user', 'service', 'payment'])
            ->latest('bookings.created_at')
            ->get();
        $filename = 'transactions-'.now()->format('Ymd-His').'.csv';

        return response()->streamDownload(function () use ($bookings): void {
            $output = fopen('php://output', 'w');

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, self::CSV_HEADERS, ';');

            foreach ($bookings as $booking) {
                fputcsv($output, [
                    $booking->booking_code,
                    $booking->payment?->order_id,
                    $booking->contact_name ?: $booking->user?->name,
                    $booking->contact_email ?: $booking->user?->email,
                    $booking->contact_phone ?: $booking->user?->phone,
                    $booking->service?->name,
                    optional($booking->visit_date)->format('Y-m-d'),
                    $booking->participant_count,
                    $booking->status,
                    $booking->payment?->status ?? Payment::STATUS_UNPAID,
                    $booking->payment?->payment_type,
                    $booking->payment?->gross_amount ?? $booking->total_price,
                    optional($booking->payment?->paid_at)->format('Y-m-d H:i:s'),
                    optional($booking->created_at)->format('Y-m-d H:i:s'),
                ], ';');
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function transactionQuery(Request $request): Builder
    {
        $query = Booking::query();
        $paymentStatus = $request->input('payment_status');

        return $query
            ->when(
                $request->filled('date_from'),
                fn ($query) => $query->whereDate('bookings.created_at', '>=', $request->input('date_from'))
            )
            ->when(
                $request->filled('date_to'),
                fn ($query) => $query->whereDate('bookings.created_at', '<=', $request->input('date_to'))
            )
            ->when(
                in_array($paymentStatus, $this->paymentStatuses(), true),
                function ($query) use ($paymentStatus): void {
                    $query->where(function ($query) use ($paymentStatus): void {
                        $query->whereHas('payment', fn ($paymentQuery) => $paymentQuery->where('status', $paymentStatus));

                        if ($paymentStatus === Payment::STATUS_UNPAID) {
                            $query->orDoesntHave('payment');
                        }
                    });
                }
            )
            ->when(
                in_array($request->input('booking_status'), Booking::STATUSES, true),
                fn ($query) => $query->where('bookings.status', $request->input('booking_status'))
            );
    }

    private function summary(Builder $baseQuery): array
    {
        $bookingIdQuery = fn () => (clone $baseQuery)->select('bookings.id');

        return [
            'total_bookings' => (clone $baseQuery)->count(),
            'total_paid_transactions' => Payment::whereIn('booking_id', $bookingIdQuery())
                ->where('status', Payment::STATUS_PAID)
                ->count(),
            'total_revenue_paid' => Payment::whereIn('booking_id', $bookingIdQuery())
                ->where('status', Payment::STATUS_PAID)
                ->sum('gross_amount'),
            'total_participants' => (clone $baseQuery)->sum('participant_count'),
            'total_cancelled_expired' => (clone $baseQuery)
                ->whereIn('status', [Booking::STATUS_CANCELLED, Booking::STATUS_EXPIRED])
                ->count(),
        ];
    }

    private function paymentStatuses(): array
    {
        return [
            Payment::STATUS_UNPAID,
            Payment::STATUS_PENDING,
            Payment::STATUS_PAID,
            Payment::STATUS_FAILED,
            Payment::STATUS_EXPIRED,
            Payment::STATUS_CANCELLED,
        ];
    }
}
