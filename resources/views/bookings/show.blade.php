@extends('layouts.app')

@section('title', 'Detail Booking')

@section('content')
@php
    $steps = [
        ['Checkout', 'waiting_payment'],
        ['Payment Confirmed', 'confirmed'],
        ['E-Ticket Ready', 'ticket'],
        ['Completed', 'completed'],
    ];

    $activeIndex = match($booking->status) {
        'waiting_payment' => 0,
        'confirmed' => $booking->ticket ? 2 : 1,
        'completed' => 3,
        default => -1,
    };
@endphp

<section class="app-container py-10 lg:py-14">
    <div class="mb-8 flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="eyebrow">Detail Booking</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight">{{ $booking->booking_code }}</h1>
            <p class="mt-2 text-[#6B7280]">{{ $booking->service->name }} - {{ $booking->visit_date->format('d M Y') }}</p>
        </div>
        <x-status-badge :status="$booking->status" />
    </div>

    <div class="grid gap-6 xl:grid-cols-[1fr_380px]">
        <div class="space-y-6">
            <div class="surface-card p-6 sm:p-7">
                <h2 class="text-2xl font-black">Timeline booking</h2>
                <div class="mt-6 grid gap-4 md:grid-cols-4">
                    @foreach($steps as $index => [$label, $status])
                        <div class="rounded-[18px] border {{ $index <= $activeIndex ? 'border-emerald-200 bg-emerald-50 text-[#007A5A]' : 'border-[#E5EAF2] bg-white text-[#6B7280]' }} p-4">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full {{ $index <= $activeIndex ? 'bg-[#007A5A] text-white' : 'bg-slate-100 text-slate-400' }}">
                                <x-icon name="check" class="h-5 w-5" />
                            </div>
                            <p class="mt-3 text-sm font-black">{{ $label }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="surface-card overflow-hidden">
                <img src="{{ asset($booking->service->image_path ?: 'assets/images/destination-waterfall.jpg') }}" alt="Paket {{ $booking->service->name }}" loading="lazy" decoding="async" class="h-56 w-full object-cover">
                <div class="p-6 sm:p-7">
                    <h2 class="text-2xl font-black">{{ $booking->service->name }}</h2>
                    <p class="mt-3 text-sm leading-7 text-[#6B7280]">{{ $booking->service->description }}</p>
                    <dl class="mt-6 grid gap-5 md:grid-cols-2">
                        <div><dt class="text-sm font-bold text-[#6B7280]">Tanggal kunjungan</dt><dd class="mt-1 text-lg font-black">{{ $booking->visit_date->format('d M Y') }}</dd></div>
                        <div><dt class="text-sm font-bold text-[#6B7280]">Jumlah peserta</dt><dd class="mt-1 text-lg font-black">{{ $booking->participant_count }} orang</dd></div>
                        <div><dt class="text-sm font-bold text-[#6B7280]">Nama pemesan</dt><dd class="mt-1 text-lg font-black">{{ $booking->contact_name }}</dd></div>
                        <div><dt class="text-sm font-bold text-[#6B7280]">Kontak</dt><dd class="mt-1 text-lg font-black">{{ $booking->contact_phone }}</dd></div>
                        <div class="md:col-span-2"><dt class="text-sm font-bold text-[#6B7280]">Email</dt><dd class="mt-1 text-lg font-black">{{ $booking->contact_email }}</dd></div>
                    </dl>
                </div>
            </div>

            @if($booking->ticket)
                <div class="surface-card p-6 sm:p-7">
                    <div class="flex flex-wrap items-center justify-between gap-4">
                        <div>
                            <p class="eyebrow">Voucher</p>
                            <h2 class="mt-2 text-2xl font-black">{{ $booking->ticket->ticket_code }}</h2>
                        </div>
                        <x-status-badge :status="$booking->ticket->status" />
                    </div>
                    <p class="mt-4 text-sm leading-7 text-[#6B7280]">E-ticket ini akan digunakan saat verifikasi kedatangan di lokasi wisata.</p>
                </div>
            @endif

            @if($booking->review)
                <div class="surface-card p-6 sm:p-7">
                    <h2 class="text-2xl font-black">Review Anda</h2>
                    <div class="mt-4"><x-rating-stars :rating="$booking->review->rating" /></div>
                    @if($booking->review->comment)
                        <p class="mt-4 text-sm leading-7 text-[#6B7280]">{{ $booking->review->comment }}</p>
                    @endif
                </div>
            @endif
        </div>

        <aside class="space-y-6">
            <div class="surface-card p-6 sm:p-7">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="eyebrow">Invoice</p>
                        <h2 class="mt-2 text-2xl font-black">Pembayaran</h2>
                    </div>
                    <x-icon name="receipt" class="h-8 w-8 text-[#007A5A]" />
                </div>
                <div class="mt-6 rounded-[20px] bg-[#EEF3FF] p-5">
                    <div class="flex items-center justify-between gap-3">
                        <span class="text-sm font-bold text-[#6B7280]">Payment status</span>
                        <x-status-badge :status="$booking->payment?->status ?? 'unpaid'" />
                    </div>
                    <div class="mt-5 flex items-center justify-between gap-4 border-t border-[#DDE6F6] pt-5">
                        <span class="text-sm font-bold text-[#6B7280]">Order ID</span>
                        <span class="text-right text-sm font-black">{{ $booking->payment?->order_id ?? '-' }}</span>
                    </div>
                    <div class="mt-4 flex items-center justify-between gap-4">
                        <span class="text-sm font-bold text-[#6B7280]">Total</span>
                        <span class="text-xl font-black text-[#007A5A]">Rp {{ number_format($booking->total_price, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="surface-card p-6 sm:p-7">
                <h2 class="text-2xl font-black">Aksi</h2>
                <div class="mt-5 space-y-3">
                    @if($booking->canPay())
                        <a href="{{ route('bookings.checkout', $booking) }}" class="btn-primary w-full"><x-icon name="receipt" class="h-5 w-5" /> Bayar Sekarang</a>
                    @endif
                    @if($booking->isTicketAvailable())
                        <a href="{{ route('my-bookings.ticket', $booking) }}" class="btn-dark w-full"><x-icon name="ticket" class="h-5 w-5" /> Lihat E-Ticket</a>
                    @endif
                    @if($booking->canBeReviewed())
                        <a href="{{ route('reviews.create', $booking) }}" class="btn-secondary w-full"><x-icon name="star" class="h-5 w-5" /> Tulis Review</a>
                    @endif
                    <a href="{{ route('my-bookings.index') }}" class="btn-secondary w-full">Kembali ke Pesanan Saya</a>
                </div>
            </div>
        </aside>
    </div>
</section>
@endsection
