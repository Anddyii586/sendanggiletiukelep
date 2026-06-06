@extends('layouts.admin')

@section('title', 'Kelola Booking')

@section('content')
<section>
    <div class="mb-8">
        <h1 class="text-4xl font-black tracking-tight">Kelola Booking</h1>
        <p class="mt-2 text-[#6B7280]">Pantau semua booking, invoice, payment Midtrans, dan e-ticket traveler.</p>
    </div>

    <form method="GET" action="{{ route('admin.bookings.index') }}" class="surface-card mb-6 grid gap-4 p-5 md:grid-cols-4">
        <select name="status" class="form-input">
            <option value="">Semua status booking</option>
            @foreach($statuses as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ str_replace('_', ' ', $status) }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-input">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-input">
        <button class="btn-dark" type="submit"><x-icon name="search" class="h-5 w-5" /> Filter</button>
    </form>

    <div class="surface-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="premium-table text-sm">
                <thead>
                    <tr>
                        <th>Booking</th>
                        <th>Traveler</th>
                        <th>Paket</th>
                        <th>Tanggal</th>
                        <th>Peserta</th>
                        <th>Total</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Aksi</th>
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
                                <div class="flex items-center gap-3">
                                    <div class="avatar-initial">{{ strtoupper(substr($booking->contact_name ?: $booking->user->name, 0, 1)) }}</div>
                                    <div><p class="font-black">{{ $booking->contact_name ?: $booking->user->name }}</p><p class="text-xs text-[#6B7280]">{{ $booking->contact_email ?: $booking->user->email }}</p></div>
                                </div>
                            </td>
                            <td class="font-semibold">{{ $booking->service->name }}</td>
                            <td>{{ $booking->visit_date->format('d M Y') }}</td>
                            <td>{{ $booking->participant_count }}</td>
                            <td class="font-bold">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                            <td><x-status-badge :status="$booking->payment?->status ?? 'unpaid'" /></td>
                            <td><x-status-badge :status="$booking->status" /></td>
                            <td><a class="text-[#007A5A]" href="{{ route('admin.bookings.show', $booking) }}"><x-icon name="eye" class="h-5 w-5" /></a></td>
                        </tr>
                    @empty
                        <tr><td class="py-8 text-[#6B7280]" colspan="9">Booking tidak ditemukan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-8">{{ $bookings->links() }}</div>
</section>
@endsection
