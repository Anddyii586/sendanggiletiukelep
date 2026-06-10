@extends('layouts.admin')

@section('title', 'Transaction Report')

@section('content')
<section>
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-4xl font-black tracking-tight">Transaction Report</h1>
            <p class="mt-2 text-[#6B7280]">Laporan ringkas booking, payment, revenue, dan peserta.</p>
        </div>
        <a href="{{ route('admin.reports.transactions.export', request()->query()) }}" class="btn-primary"><x-icon name="receipt" class="h-5 w-5" /> Export CSV</a>
    </div>

    <form method="GET" action="{{ route('admin.reports.transactions') }}" class="surface-card mb-6 grid gap-4 p-5 md:grid-cols-5">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input">
        <select name="payment_status" class="form-input">
            <option value="">Semua payment status</option>
            @foreach($paymentStatuses as $status)
                <option value="{{ $status }}" @selected(request('payment_status') === $status)>{{ str_replace('_', ' ', $status) }}</option>
            @endforeach
        </select>
        <select name="booking_status" class="form-input">
            <option value="">Semua booking status</option>
            @foreach($bookingStatuses as $status)
                <option value="{{ $status }}" @selected(request('booking_status') === $status)>{{ str_replace('_', ' ', $status) }}</option>
            @endforeach
        </select>
        <button class="btn-dark" type="submit"><x-icon name="search" class="h-5 w-5" /> Filter</button>
    </form>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-5">
        @foreach([
            ['Total Booking', $summary['total_bookings'], 'calendar', 'text-sky-600'],
            ['Paid Transaction', $summary['total_paid_transactions'], 'check', 'text-[#007A5A]'],
            ['Revenue Paid', 'Rp '.number_format($summary['total_revenue_paid'], 0, ',', '.'), 'receipt', 'text-[#007A5A]'],
            ['Total Participant', $summary['total_participants'], 'user', 'text-teal-600'],
            ['Cancelled/Expired', $summary['total_cancelled_expired'], 'x', 'text-red-600'],
        ] as [$label, $value, $icon, $color])
            <article class="surface-card p-6">
                <x-icon :name="$icon" class="h-7 w-7 {{ $color }}" />
                <p class="mt-8 text-sm font-black text-[#4B5563]">{{ $label }}</p>
                <p class="mt-3 text-3xl font-black">{{ $value }}</p>
            </article>
        @endforeach
    </div>

    <div class="surface-card mt-8 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="premium-table text-sm">
                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>Customer</th>
                        <th>Package</th>
                        <th>Visit</th>
                        <th>Participants</th>
                        <th>Booking Status</th>
                        <th>Payment</th>
                        <th>Gross Amount</th>
                        <th>Paid At</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td>
                                <p class="font-black">{{ $booking->booking_code }}</p>
                                <p class="text-xs text-[#6B7280]">{{ $booking->payment?->order_id ?? '-' }}</p>
                            </td>
                            <td>
                                <p class="font-black">{{ $booking->contact_name ?: $booking->user?->name }}</p>
                                <p class="text-xs text-[#6B7280]">{{ $booking->contact_email ?: $booking->user?->email }}</p>
                            </td>
                            <td class="font-semibold">{{ $booking->service?->name ?? '-' }}</td>
                            <td>{{ optional($booking->visit_date)->format('d M Y') }}</td>
                            <td>{{ $booking->participant_count }}</td>
                            <td><x-status-badge :status="$booking->status" /></td>
                            <td><x-status-badge :status="$booking->payment?->status ?? 'unpaid'" /></td>
                            <td class="font-bold">Rp {{ number_format($booking->payment?->gross_amount ?? $booking->total_price, 0, ',', '.') }}</td>
                            <td>{{ optional($booking->payment?->paid_at)->format('d M Y H:i') ?? '-' }}</td>
                            <td>{{ $booking->created_at->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td class="py-8 text-[#6B7280]" colspan="10">Transaksi tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8">{{ $bookings->links() }}</div>
</section>
@endsection
