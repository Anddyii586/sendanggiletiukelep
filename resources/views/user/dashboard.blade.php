@extends('layouts.traveler')

@section('title', 'Pesanan Saya')

@section('content')
@php
    $reviewCount = auth()->user()->reviews()->count();
@endphp

<section>
    <div class="flex flex-wrap items-start justify-between gap-5">
        <div>
            <h1 class="text-4xl font-black tracking-tight">Halo, {{ auth()->user()->name }}</h1>
            <p class="mt-2 text-lg text-[#6B7280]">Siap untuk petualangan berikutnya di Lombok Utara?</p>
        </div>
        <a href="{{ route('bookings.create') }}" class="btn-primary"><x-icon name="plus" class="h-5 w-5" /> Booking Sekarang</a>
    </div>

    <div class="mt-10 grid gap-6 md:grid-cols-2 xl:grid-cols-4">
        <article class="surface-card flex items-center gap-6 p-6">
            <div class="flex h-16 w-16 items-center justify-center rounded-[18px] bg-emerald-50 text-[#007A5A]"><x-icon name="ticket" class="h-8 w-8" /></div>
            <div><p class="text-xs font-black uppercase tracking-[0.14em] text-[#6B7280]">Total Booking</p><p class="text-4xl font-black">{{ $totalBookings }}</p></div>
        </article>
        <article class="surface-card flex items-center gap-6 p-6">
            <div class="flex h-16 w-16 items-center justify-center rounded-[18px] bg-orange-50 text-orange-600"><x-icon name="receipt" class="h-8 w-8" /></div>
            <div><p class="text-xs font-black uppercase tracking-[0.14em] text-[#6B7280]">Waiting Payment</p><p class="text-4xl font-black">{{ $waitingPaymentBookings }}</p></div>
        </article>
        <article class="surface-card flex items-center gap-6 p-6">
            <div class="flex h-16 w-16 items-center justify-center rounded-[18px] bg-sky-50 text-sky-600"><x-icon name="check" class="h-8 w-8" /></div>
            <div><p class="text-xs font-black uppercase tracking-[0.14em] text-[#6B7280]">Confirmed</p><p class="text-4xl font-black">{{ $confirmedBookings }}</p></div>
        </article>
        <article class="surface-card flex items-center gap-6 p-6">
            <div class="flex h-16 w-16 items-center justify-center rounded-[18px] bg-stone-100 text-stone-600"><x-icon name="star" class="h-8 w-8" /></div>
            <div><p class="text-xs font-black uppercase tracking-[0.14em] text-[#6B7280]">Completed</p><p class="text-4xl font-black">{{ $completedBookings }}</p></div>
        </article>
    </div>

    <div class="mt-10 grid gap-6 xl:grid-cols-[1fr_.92fr]">
        <section class="surface-card overflow-hidden">
            <div class="flex items-center justify-between gap-4 p-6">
                <h2 class="text-2xl font-black">Booking Terakhir</h2>
                <a href="{{ route('my-bookings.index') }}" class="text-sm font-black text-[#007A5A]">Lihat Semua</a>
            </div>
            <div class="hidden overflow-x-auto md:block">
                <table class="premium-table text-sm">
                    <thead class="table-head">
                        <tr>
                            <th class="px-6 py-4">Destinasi</th>
                            <th class="px-6 py-4">Tanggal</th>
                            <th class="px-6 py-4">Payment</th>
                            <th class="px-6 py-4">Status</th>
                            <th class="px-6 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E5EAF2]">
                        @forelse($latestBookings as $booking)
                            <tr>
                                <td class="px-6 py-5">
                                    <div class="flex items-center gap-4">
                                        <img src="{{ asset('assets/images/gallery-1.jpg') }}" alt="Thumbnail destinasi air terjun" loading="lazy" decoding="async" class="h-12 w-12 rounded-[12px] object-cover">
                                        <div><p class="font-black">{{ $booking->service->name }}</p><p class="text-xs text-[#6B7280]">Sendang Gile & Tiu Kelep</p></div>
                                    </div>
                                </td>
                                <td class="px-6 py-5 font-semibold text-[#374151]">{{ $booking->visit_date->format('d M Y') }}</td>
                                <td class="px-6 py-5"><x-status-badge :status="$booking->payment?->status ?? 'unpaid'" /></td>
                                <td class="px-6 py-5"><x-status-badge :status="$booking->status" /></td>
                                <td class="px-6 py-5">
                                    @if($booking->canPay())
                                        <a href="{{ route('bookings.checkout', $booking) }}" class="text-sm font-black text-[#007A5A]">Bayar</a>
                                    @elseif($booking->isTicketAvailable())
                                        <a href="{{ route('my-bookings.ticket', $booking) }}" class="text-sm font-black text-[#007A5A]">E-Ticket</a>
                                    @else
                                        <a href="{{ route('my-bookings.show', $booking) }}" class="text-sm font-black text-[#007A5A]">Detail</a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td class="px-6 py-8 text-[#6B7280]" colspan="5">Belum ada booking.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="grid gap-3 p-5 md:hidden">
                @forelse($latestBookings as $booking)
                    <a href="{{ route('my-bookings.show', $booking) }}" class="rounded-[18px] border border-[#E5EAF2] p-4">
                        <p class="font-black">{{ $booking->service->name }}</p>
                        <p class="mt-1 text-sm text-[#6B7280]">{{ $booking->visit_date->format('d M Y') }}</p>
                        <x-status-badge :status="$booking->status" class="mt-3" />
                    </a>
                @empty
                    <p class="p-4 text-[#6B7280]">Belum ada booking.</p>
                @endforelse
            </div>
        </section>

        <a href="{{ route('destination') }}" class="relative min-h-[300px] overflow-hidden rounded-[22px] bg-cover bg-center p-8 text-white shadow-[0_24px_50px_rgba(15,27,45,.18)]" style="background-image: linear-gradient(90deg, rgba(15,27,45,.76), rgba(15,27,45,.22)), url('{{ asset('assets/images/booking-preview.jpg') }}')">
            <span class="inline-flex rounded-full bg-[#10B981] px-4 py-2 text-xs font-black uppercase text-[#07362d]">Rekomendasi Minggu Ini</span>
            <h2 class="mt-9 max-w-md text-4xl font-black leading-tight">Sunrise at Senaru Hill</h2>
            <p class="mt-4 max-w-md text-sm leading-7 text-slate-100">Nikmati pemandangan matahari terbit terbaik di kaki Gunung Rinjani sebelum trekking ke air terjun.</p>
            <span class="mt-8 inline-flex items-center gap-2 border-b-2 border-[#10B981] pb-2 text-sm font-black">Eksplor Sekarang -></span>
        </a>
    </div>
</section>
@endsection
