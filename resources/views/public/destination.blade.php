@extends('layouts.app')

@section('title', 'Informasi Destinasi')

@section('content')
@php
    $mainService = $services->first();
    $bookingRoute = route('bookings.create', ['package' => $mainService?->id]);
@endphp

<section class="app-container py-10 lg:py-14">
    <div class="grid gap-9 lg:grid-cols-[1fr_360px]">
        <div>
            <img src="{{ asset('assets/images/destination-waterfall.jpg') }}" alt="Air Terjun Senaru" decoding="async" class="h-[420px] w-full rounded-[22px] object-cover shadow-[0_18px_45px_rgba(15,27,45,.12)]">
            <div class="mt-9">
                <h1 class="text-3xl font-black tracking-tight text-[#007A5A] md:text-4xl">Keajaiban Air Terjun Kembar Senaru</h1>
                <p class="mt-5 max-w-4xl text-base leading-8 text-[#6B7280]">
                    {{ $settings['destination_description'] ?? 'Terletak di kaki Gunung Rinjani, Sendang Gile dan Tiu Kelep adalah permata tersembunyi di Lombok Utara. Sendang Gile menyapa Anda dengan tingkatan air yang elegan, sementara Tiu Kelep menawarkan petualangan hutan tropis menuju pancuran air raksasa yang menciptakan kabut abadi.' }}
                </p>
            </div>

            <div class="mt-8 grid gap-5 md:grid-cols-2">
                <article class="rounded-[18px] bg-[#EEF3FF] p-6">
                    <h2 class="text-xs font-black uppercase tracking-[0.16em] text-[#007A5A]">Fasilitas</h2>
                    <div class="mt-5 grid gap-3 text-sm font-semibold text-[#374151]">
                        <p class="flex gap-3"><x-icon name="guide" class="h-5 w-5 text-[#007A5A]" /> Gazebo & area istirahat</p>
                        <p class="flex gap-3"><x-icon name="user" class="h-5 w-5 text-[#007A5A]" /> Toilet & ruang ganti</p>
                        <p class="flex gap-3"><x-icon name="map-pin" class="h-5 w-5 text-[#007A5A]" /> Area parkir luas</p>
                        <p class="flex gap-3"><x-icon name="droplet" class="h-5 w-5 text-[#007A5A]" /> Jalur trekking terawat</p>
                    </div>
                </article>
                <article class="rounded-[18px] bg-[#EEF3FF] p-6">
                    <h2 class="text-xs font-black uppercase tracking-[0.16em] text-[#007A5A]">Info Operasional</h2>
                    <div class="mt-5 divide-y divide-[#DDE6F6] text-sm">
                        <div class="flex justify-between gap-4 pb-4"><span class="text-[#6B7280]">Jam kunjungan</span><strong>{{ $settings['opening_hours'] ?? '07.00 - 17.00' }}</strong></div>
                        <div class="flex justify-between gap-4 py-4"><span class="text-[#6B7280]">Tiket masuk</span><strong class="text-[#007A5A]">Mulai Rp {{ $mainService ? number_format($mainService->price, 0, ',', '.') : '10.000' }}</strong></div>
                        <div class="flex justify-between gap-4 pt-4"><span class="text-[#6B7280]">Lokasi</span><strong>Senaru</strong></div>
                    </div>
                </article>
            </div>
        </div>

        <aside class="space-y-6">
            <div class="surface-card p-7">
                <div class="flex items-center gap-2 text-sm font-black text-[#007A5A]">
                    <x-rating-stars rating="5" class="h-4 w-4 text-[#007A5A]" />
                    <span>4.9 <span class="text-xs text-[#6B7280]">(1.240 Reviews)</span></span>
                </div>
                <h2 class="mt-4 text-2xl font-black">Siap Berpetualang?</h2>
                <p class="mt-2 text-sm leading-6 text-[#6B7280]">Booking pemandu lokal profesional untuk pengalaman terbaik.</p>
                <div class="mt-6 rounded-[16px] bg-[#F5F7FB] p-4">
                    <p class="text-xs font-bold uppercase text-[#6B7280]">Paket populer</p>
                    <div class="mt-1 flex items-end justify-between gap-4">
                        <strong>{{ $mainService->name ?? '2 Air Terjun + Guide' }}</strong>
                        <span class="font-black text-[#007A5A]">Rp {{ $mainService ? number_format($mainService->price / 1000, 0, ',', '.') : '150' }}k</span>
                    </div>
                </div>
                <a href="{{ $bookingRoute }}" class="btn-primary mt-5 w-full"><x-icon name="ticket" class="h-5 w-5" /> Booking Sekarang</a>
                <p class="mt-4 text-center text-xs font-semibold text-[#6B7280]">Checkout online & e-ticket setelah pembayaran</p>
            </div>

            <div class="surface-card p-7">
                <h3 class="text-sm font-black">Lokasi Kami</h3>
                <div class="mt-4 flex h-48 items-center justify-center rounded-[16px] bg-[#DDE6F0] text-[#007A5A]">
                    <x-icon name="map-pin" class="h-10 w-10" />
                </div>
            </div>
        </aside>
    </div>
</section>

<section class="bg-[#EAF0FF] py-20">
    <div class="app-container">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="section-title">Galeri Destinasi</h2>
            <p class="mt-4 text-sm leading-7 text-[#6B7280]">Setiap sudut di Sendang Gile dan Tiu Kelep adalah keajaiban alam yang siap untuk diabadikan.</p>
        </div>
        <div class="gallery-grid mt-12 grid auto-rows-[190px] gap-5 md:grid-cols-6">
            <img src="{{ asset('assets/images/gallery-1.jpg') }}" alt="Galeri air terjun" loading="lazy" decoding="async" class="h-full w-full rounded-[18px] object-cover md:col-span-3 md:row-span-2">
            <img src="{{ asset('assets/images/gallery-2.jpg') }}" alt="Jalur hutan Senaru" loading="lazy" decoding="async" class="h-full w-full rounded-[18px] object-cover md:col-span-2">
            <img src="{{ asset('assets/images/gallery-3.jpg') }}" alt="Air terjun tinggi" loading="lazy" decoding="async" class="h-full w-full rounded-[18px] object-cover md:col-span-1 md:row-span-2">
            <img src="{{ asset('assets/images/gallery-4.jpg') }}" alt="Tangga hutan menuju air terjun" loading="lazy" decoding="async" class="h-full w-full rounded-[18px] object-cover md:col-span-2">
            <img src="{{ asset('assets/images/gallery-5.jpg') }}" alt="Hutan Senaru" loading="lazy" decoding="async" class="h-full w-full rounded-[18px] object-cover md:col-span-3">
        </div>
    </div>
</section>

<section class="app-container py-20">
    <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="section-title">Apa Kata Mereka?</h2>
            <div class="mt-4 flex items-center gap-3">
                <x-rating-stars rating="5" />
                <strong class="text-xl">4.9 / 5.0</strong>
                <span class="text-sm text-[#6B7280]">Berdasarkan 1.2k review</span>
            </div>
        </div>
        <a href="{{ route('reviews') }}" class="btn-primary">Tulis Review</a>
    </div>

    <div class="grid gap-5 md:grid-cols-3">
        @foreach(['Andi Pratama', 'Maria Garcia', 'Budi Santoso'] as $name)
            <article class="surface-card p-6">
                <div class="flex items-center justify-between gap-3">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-sm font-black text-[#007A5A]">{{ strtoupper(substr($name, 0, 1)) }}</div>
                        <div><p class="text-sm font-black">{{ $name }}</p><p class="text-xs text-[#6B7280]">05 Jan 2024</p></div>
                    </div>
                    <x-rating-stars rating="5" class="h-3.5 w-3.5 text-[#007A5A]" />
                </div>
                <p class="mt-5 text-sm leading-7 text-[#6B7280]">Pengalaman yang luar biasa. Trekking nyaman, airnya segar, dan pemandangannya benar-benar indah.</p>
            </article>
        @endforeach
    </div>
</section>

<section class="topographic-bg py-20">
    <div class="app-container">
        <div class="max-w-md rounded-[26px] bg-white p-8 shadow-[0_24px_45px_rgba(15,27,45,.18)]">
            <h2 class="text-3xl font-black text-[#007A5A]">Hubungi Kami</h2>
            <div class="mt-8 space-y-5 text-sm text-[#374151]">
                <p class="flex gap-3"><x-icon name="map-pin" class="h-5 w-5 text-[#007A5A]" /> {{ $settings['address'] ?? 'Jl. Pariwisata Senaru, Lombok Utara' }}</p>
                <p class="flex gap-3"><x-icon name="phone" class="h-5 w-5 text-[#007A5A]" /> {{ $settings['contact_phone'] ?? '+62 812 3456 7890' }}</p>
                <p class="flex gap-3"><x-icon name="clock" class="h-5 w-5 text-[#007A5A]" /> {{ $settings['opening_hours'] ?? '08.00 - 17.00 WITA' }}</p>
            </div>
        </div>
    </div>
</section>
@endsection
