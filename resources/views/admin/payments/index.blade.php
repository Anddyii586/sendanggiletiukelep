@extends('layouts.admin')

@section('title', 'Payment Monitoring')

@section('content')
<section>
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <h1 class="text-4xl font-black tracking-tight">Payment Monitoring</h1>
            <p class="mt-2 text-[#6B7280]">Pantau transaksi Midtrans tanpa mengubah flow pembayaran traveler.</p>
        </div>
    </div>

    <form method="GET" action="{{ route('admin.payments.index') }}" class="surface-card mb-6 grid gap-4 p-5 md:grid-cols-3 xl:grid-cols-7">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Order ID, booking, nama, kontak" class="form-input xl:col-span-2">
        <select name="status" class="form-input">
            <option value="">Semua payment status</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', $status) }}</option>
            @endforeach
        </select>
        <select name="payment_type" class="form-input">
            <option value="">Semua payment type</option>
            @foreach($paymentTypes as $type)
                <option value="{{ $type }}" @selected(request('payment_type') === $type)>{{ $type }}</option>
            @endforeach
        </select>
        <select name="date_column" class="form-input">
            @foreach($dateColumns as $column => $label)
                <option value="{{ $column }}" @selected($dateColumn === $column)>{{ $label }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input">
        <button class="btn-dark md:col-span-3 xl:col-span-1" type="submit"><x-icon name="search" class="h-5 w-5" /> Filter</button>
    </form>

    <div class="surface-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="premium-table text-sm">
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Booking Code</th>
                        <th>User/contact</th>
                        <th>Package/service</th>
                        <th>Gross Amount</th>
                        <th>Payment Status</th>
                        <th>Transaction Status</th>
                        <th>Payment Type</th>
                        <th>Fraud Status</th>
                        <th>Paid At</th>
                        <th>Created At</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td class="font-black">{{ $payment->order_id ?? '-' }}</td>
                            <td>{{ $payment->booking?->booking_code ?? '-' }}</td>
                            <td>
                                <p class="font-black">{{ $payment->booking?->contact_name ?: $payment->booking?->user?->name ?? '-' }}</p>
                                <p class="text-xs text-[#6B7280]">{{ $payment->booking?->contact_email ?: $payment->booking?->user?->email ?? '-' }}</p>
                            </td>
                            <td class="font-semibold">{{ $payment->booking?->service?->name ?? '-' }}</td>
                            <td class="font-bold">Rp {{ number_format($payment->gross_amount, 0, ',', '.') }}</td>
                            <td><x-status-badge :status="$payment->status" /></td>
                            <td>{{ $payment->transaction_status ?? '-' }}</td>
                            <td>{{ $payment->payment_type ?? '-' }}</td>
                            <td>{{ $payment->fraud_status ?? '-' }}</td>
                            <td>{{ optional($payment->paid_at)->format('d M Y H:i') ?? '-' }}</td>
                            <td>{{ $payment->created_at->format('d M Y H:i') }}</td>
                            <td>
                                @if($payment->booking)
                                    <a class="text-[#007A5A]" href="{{ route('admin.bookings.show', $payment->booking) }}"><x-icon name="eye" class="h-5 w-5" /></a>
                                @else
                                    <span class="text-[#9CA3AF]">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td class="py-8 text-[#6B7280]" colspan="12">Payment tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8">{{ $payments->links() }}</div>
</section>
@endsection
