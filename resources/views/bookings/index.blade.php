@extends('layouts.app')

@section('title', 'Riwayat Booking')

@section('content')
<section class="app-container py-10 lg:py-14">
    <div class="mb-8 flex flex-wrap items-center justify-between gap-4">
        <div>
            <p class="eyebrow">My Trips</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight">Riwayat Booking</h1>
            <p class="mt-2 text-[#6B7280]">Pantau invoice, pembayaran, dan e-ticket dari semua perjalanan Anda.</p>
        </div>
        <a href="{{ route('bookings.create') }}" class="btn-primary"><x-icon name="plus" class="h-5 w-5" /> Pesan Paket Baru</a>
    </div>

    <div class="surface-card overflow-hidden">
        <div class="hidden overflow-x-auto lg:block">
            <table class="premium-table text-sm">
                <thead class="table-head">
                    <tr>
                        <th>Booking</th>
                        <th>Paket</th>
                        <th>Tanggal</th>
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
                                <p class="text-xs text-[#6B7280]">{{ $booking->created_at->format('d M Y H:i') }}</p>
                            </td>
                            <td class="font-black">{{ $booking->service->name }}</td>
                            <td>{{ $booking->visit_date->format('d M Y') }}</td>
                            <td class="font-bold">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</td>
                            <td><x-status-badge :status="$booking->payment?->status ?? 'unpaid'" /></td>
                            <td><x-status-badge :status="$booking->status" /></td>
                            <td>
                                <div class="flex flex-wrap gap-2">
                                    <a class="btn-secondary px-3 py-2" href="{{ route('my-bookings.show', $booking) }}"><x-icon name="eye" class="h-4 w-4" /></a>
                                    @if($booking->canPay())
                                        <a class="btn-primary px-3 py-2" href="{{ route('bookings.checkout', $booking) }}">Bayar</a>
                                    @endif
                                    @if($booking->isTicketAvailable())
                                        <a class="btn-dark px-3 py-2" href="{{ route('my-bookings.ticket', $booking) }}">E-Ticket</a>
                                    @endif
                                    @if($booking->canBeReviewed())
                                        <a class="btn-secondary px-3 py-2" href="{{ route('reviews.create', $booking) }}">Review</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td class="py-10 text-[#6B7280]" colspan="7">Belum ada booking.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="grid gap-4 p-4 lg:hidden">
            @forelse($bookings as $booking)
                <article class="rounded-[20px] border border-[#E5EAF2] bg-white p-5">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.14em] text-[#007A5A]">{{ $booking->booking_code }}</p>
                            <h2 class="mt-2 text-lg font-black">{{ $booking->service->name }}</h2>
                        </div>
                        <x-status-badge :status="$booking->status" />
                    </div>
                    <dl class="mt-5 grid gap-3 text-sm">
                        <div class="flex justify-between gap-4"><dt class="text-[#6B7280]">Tanggal</dt><dd class="font-black">{{ $booking->visit_date->format('d M Y') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-[#6B7280]">Total</dt><dd class="font-black">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</dd></div>
                        <div class="flex justify-between gap-4"><dt class="text-[#6B7280]">Payment</dt><dd><x-status-badge :status="$booking->payment?->status ?? 'unpaid'" /></dd></div>
                    </dl>
                    <div class="mt-5 flex flex-wrap gap-2">
                        <a class="btn-secondary px-4 py-2" href="{{ route('my-bookings.show', $booking) }}">Detail</a>
                        @if($booking->canPay())
                            <a class="btn-primary px-4 py-2" href="{{ route('bookings.checkout', $booking) }}">Bayar</a>
                        @endif
                        @if($booking->isTicketAvailable())
                            <a class="btn-dark px-4 py-2" href="{{ route('my-bookings.ticket', $booking) }}">E-Ticket</a>
                        @endif
                    </div>
                </article>
            @empty
                <p class="rounded-[18px] bg-[#F7F8FC] p-5 text-[#6B7280]">Belum ada booking.</p>
            @endforelse
        </div>
    </div>

    <div class="mt-8">{{ $bookings->links() }}</div>
</section>
@endsection
