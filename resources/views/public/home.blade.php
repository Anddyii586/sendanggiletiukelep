@extends('layouts.app')

@section('title', $settings['destination_name'] ?? 'Sendang Gile & Tiu Kelep Waterfalls')
@section('nav_variant', 'overlay')

@section('content')
@php
    $bookingRoute = route('bookings.create');
    $services = $services->values();
    $galleryFallbacks = [
        'assets/images/gallery-1.jpg',
        'assets/images/gallery-2.jpg',
        'assets/images/gallery-3.jpg',
        'assets/images/gallery-4.jpg',
        'assets/images/gallery-5.jpg',
    ];
@endphp

<section class="landing-hero relative overflow-hidden text-white">
    <div class="absolute inset-x-0 bottom-0 h-32 bg-gradient-to-b from-transparent to-[#F7F8FC] sm:h-40"></div>
    <div class="app-container relative flex min-h-[720px] items-center justify-center pb-28 pt-28 text-center sm:min-h-[760px] lg:min-h-screen lg:pb-36 lg:pt-32">
        <div class="w-full max-w-5xl">
            <div class="mb-5 flex flex-wrap justify-center gap-3 sm:mb-6">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/14 px-3 py-2 text-xs font-bold text-white ring-1 ring-white/20 backdrop-blur sm:px-4">
                    <x-icon name="map-pin" class="h-4 w-4" /> Senaru, Lombok Utara
                </span>
                <span class="inline-flex items-center gap-2 rounded-full bg-white/14 px-3 py-2 text-xs font-bold text-white ring-1 ring-white/20 backdrop-blur sm:px-4">
                    <x-icon name="guide" class="h-4 w-4" /> Guide Available
                </span>
            </div>
            <h1 class="mx-auto max-w-4xl text-4xl font-black leading-tight tracking-tight sm:text-5xl lg:text-6xl">
                Jelajahi <span class="font-serif italic text-[#67FFD0]">Keindahan</span><br>
                Sendang Gile & Tiu Kelep
            </h1>
            <p class="mx-auto mt-5 max-w-2xl text-sm leading-7 text-slate-100 sm:mt-6 sm:text-base sm:leading-8 lg:text-lg">
                Temukan surga tersembunyi di lereng Gunung Rinjani. Nikmati kesegaran air terjun kembar paling ikonik di Senaru dengan pengalaman wisata yang aman dan berkesan.
            </p>
            <div class="mt-7 flex flex-wrap justify-center gap-3 sm:mt-9 sm:gap-4">
                <a href="{{ $bookingRoute }}" class="btn-primary">Booking Sekarang <span aria-hidden="true">-></span></a>
                <a href="{{ route('gallery') }}" class="inline-flex items-center justify-center gap-2 rounded-[14px] bg-white/16 px-5 py-3 text-sm font-bold text-white ring-1 ring-white/25 backdrop-blur transition hover:bg-white/24">
                    <x-icon name="camera" class="h-5 w-5" /> Lihat Galeri
                </a>
            </div>
        </div>
    </div>
</section>

<section class="app-container relative z-10 -mt-16 pb-20 sm:-mt-20 lg:-mt-24">
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 lg:gap-5">
        @foreach([
            ['Air Terjun Alami', 'Keindahan air terjun bertingkat dengan debit air jernih dan lingkungan hutan tropis.', 'droplet'],
            ['Jasa Guide', 'Pemandu lokal profesional siap menemani perjalanan trekking Anda dengan aman.', 'guide'],
            ['Booking Online', 'Sistem pemesanan yang mudah dan cepat untuk memastikan slot kunjungan Anda.', 'ticket'],
            ['Akses Mudah', 'Tersedia rute dan informasi lokasi yang jelas menuju pintu masuk kawasan wisata.', 'map-pin'],
        ] as [$title, $desc, $icon])
            <article class="soft-card p-5 sm:p-6">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-[#007A5A]">
                    <x-icon :name="$icon" class="h-6 w-6" />
                </div>
                <h2 class="mt-5 text-lg font-black sm:mt-6">{{ $title }}</h2>
                <p class="mt-3 text-sm leading-6 text-[#6B7280]">{{ $desc }}</p>
            </article>
        @endforeach
    </div>
</section>

<section class="app-container pb-24">
    <div class="grid items-center gap-12 lg:grid-cols-[.95fr_1.05fr]">
        <div class="relative">
            <img src="{{ asset('assets/images/destination-waterfall.jpg') }}" alt="Sendang Gile waterfall" loading="lazy" decoding="async" class="h-[560px] w-full rounded-[30px] object-cover shadow-[0_24px_50px_rgba(15,27,45,0.16)]">
            <div class="absolute -bottom-8 right-8 w-56 rounded-[22px] bg-white p-6 shadow-[0_24px_45px_rgba(15,27,45,.16)]">
                <p class="eyebrow">Ketinggian</p>
                <p class="mt-2 text-4xl font-black text-[#111827]">45</p>
                <p class="text-lg font-black text-[#111827]">Meter</p>
                <p class="mt-3 text-xs leading-5 text-[#6B7280]">Salah satu air terjun tertinggi dan paling ikonik di Pulau Lombok.</p>
            </div>
        </div>

        <div>
            <p class="eyebrow">Tentang Destinasi</p>
            <h2 class="mt-4 text-4xl font-black leading-tight tracking-tight md:text-5xl">
                Dua Keajaiban Alam<br>
                <span class="font-serif italic text-[#007A5A]">Satu Perjalanan</span>
            </h2>
            <p class="mt-6 max-w-2xl text-base leading-8 text-[#6B7280]">
                Sendang Gile dan Tiu Kelep adalah permata tersembunyi di kaki Gunung Rinjani. Trekking ringan, udara sejuk, dan suara air terjun menciptakan pengalaman alam yang menenangkan.
            </p>
            <div class="mt-8 grid gap-4 sm:grid-cols-2">
                <div class="rounded-[18px] bg-[#EEF3FF] p-5">
                    <div class="flex items-center gap-3 text-[#007A5A]"><x-icon name="clock" class="h-5 w-5" /><span class="text-sm font-black">Jam Operasional</span></div>
                    <p class="mt-3 text-sm font-bold text-[#111827]">{{ $settings['opening_hours'] ?? '07.00 - 17.00 WITA' }}</p>
                </div>
                <div class="rounded-[18px] bg-[#EEF3FF] p-5">
                    <div class="flex items-center gap-3 text-[#007A5A]"><x-icon name="map-pin" class="h-5 w-5" /><span class="text-sm font-black">Fasilitas</span></div>
                    <p class="mt-3 text-sm font-bold text-[#111827]">Parkir, toilet, guide, spot foto</p>
                </div>
            </div>
            <a href="{{ route('destination') }}" class="btn-primary mt-8">Jelajahi Sekarang</a>
        </div>
    </div>
</section>

<section class="hero-soft-blue py-24">
    <div class="app-container">
        <div class="mx-auto max-w-2xl text-center">
            <h2 class="section-title">Layanan & Tiket</h2>
            <p class="mt-4 text-sm leading-7 text-[#6B7280]">Pilih paket yang sesuai untuk petualangan Anda di Sendang Gile & Tiu Kelep.</p>
        </div>
        <div class="mt-12 grid gap-6 lg:grid-cols-3">
            @forelse($services->take(3) as $service)
                @php $featured = $loop->iteration === 2; @endphp
                <article class="{{ $featured ? 'rounded-[24px] bg-[#0F1B2D] text-white shadow-[0_30px_55px_rgba(15,27,45,.25)] lg:-mt-5' : 'surface-card text-[#111827]' }} p-8">
                    <div class="flex items-start justify-between">
                        <span class="{{ $featured ? 'bg-[#10B981]/15 text-[#67FFD0]' : 'bg-[#EEF3FF] text-[#667085]' }} rounded-full px-3 py-1 text-[10px] font-black uppercase tracking-wider">
                            {{ $featured ? 'Adventure' : ($loop->first ? 'Regular' : 'Relax') }}
                        </span>
                        <x-icon name="ticket" class="h-8 w-8 {{ $featured ? 'text-white/20' : 'text-slate-200' }}" />
                    </div>
                    <h3 class="mt-8 text-xl font-black">{{ $service->name }}</h3>
                    <div class="mt-5 flex items-end gap-1">
                        <span class="{{ $featured ? 'text-[#67FFD0]' : 'text-[#007A5A]' }} text-3xl font-black">Rp {{ number_format($service->price / 1000, 0, ',', '.') }}k</span>
                        <span class="{{ $featured ? 'text-slate-300' : 'text-[#6B7280]' }} text-xs font-semibold">/pax</span>
                    </div>
                    <ul class="mt-6 space-y-3 text-sm {{ $featured ? 'text-slate-200' : 'text-[#6B7280]' }}">
                        <li class="flex gap-2"><x-icon name="check" class="h-4 w-4 text-[#10B981]" /> {{ $service->description ?: 'Akses layanan wisata resmi' }}</li>
                        <li class="flex gap-2"><x-icon name="check" class="h-4 w-4 text-[#10B981]" /> Checkout Midtrans dan e-ticket otomatis</li>
                    </ul>
                    <a href="{{ route('packages.show', $service) }}" class="{{ $featured ? 'btn-primary mt-8 w-full' : 'btn-secondary mt-8 w-full' }}">{{ $featured ? 'Booking Sekarang' : 'Lihat Paket' }}</a>
                </article>
            @empty
                <div class="surface-card p-8 text-center text-[#6B7280] lg:col-span-3">Layanan belum tersedia.</div>
            @endforelse
        </div>
    </div>
</section>

<section class="app-container py-24">
    <div class="mb-10 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h2 class="section-title">Galeri Keindahan</h2>
            <p class="mt-3 max-w-xl text-sm leading-7 text-[#6B7280]">Visual petualangan tak terlupakan di jantung Senaru, Lombok Utara.</p>
        </div>
        <a href="{{ route('gallery') }}" class="text-sm font-black text-[#007A5A] hover:text-[#005f46]">Lihat Semua Galeri -></a>
    </div>
    <div class="gallery-grid grid auto-rows-[180px] gap-5 md:grid-cols-4">
        @forelse($galleries as $gallery)
            @php $index = $loop->index; @endphp
            <img src="{{ $gallery->image_url }}" alt="{{ $gallery->title }}" loading="lazy" decoding="async" class="{{ $index === 0 ? 'md:row-span-2' : '' }} {{ $index === 1 ? 'md:col-span-2' : '' }} {{ $index === 4 ? 'md:col-span-2' : '' }} h-full w-full rounded-[22px] object-cover shadow-[0_16px_32px_rgba(15,27,45,.08)]">
        @empty
            @foreach($galleryFallbacks as $index => $fallback)
                <img src="{{ asset($fallback) }}" alt="Galeri destinasi {{ $index + 1 }}" loading="lazy" decoding="async" class="{{ $index === 0 ? 'md:row-span-2' : '' }} {{ $index === 1 ? 'md:col-span-2' : '' }} {{ $index === 4 ? 'md:col-span-2' : '' }} h-full w-full rounded-[22px] object-cover shadow-[0_16px_32px_rgba(15,27,45,.08)]">
            @endforeach
        @endforelse
    </div>
</section>

<section class="bg-[#DDE8FF] py-24">
    <div class="app-container">
        <h2 class="section-title text-center">Apa Kata Pengunjung</h2>
        <div class="mx-auto mt-3 h-1 w-20 rounded-full bg-[#007A5A]"></div>
        <div class="mt-12 grid gap-6 lg:grid-cols-3">
            @forelse($reviews as $review)
                <article class="review-card surface-card p-7">
                    <x-rating-stars :rating="$review->rating" />
                    <p class="mt-5 text-sm leading-7 text-[#4B5563]">"{{ $review->comment }}"</p>
                    <div class="mt-6 flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-sm font-black text-[#007A5A]">{{ strtoupper(substr($review->user->name, 0, 1)) }}</div>
                        <div>
                            <p class="text-sm font-black">{{ $review->user->name }}</p>
                            <p class="text-xs text-[#6B7280]">Wisatawan</p>
                        </div>
                    </div>
                </article>
            @empty
                @foreach(['Andi Natalia', 'James Miller', 'Bambang Prasetyo'] as $name)
                    <article class="review-card surface-card p-7">
                        <x-rating-stars rating="5" />
                        <p class="mt-5 text-sm leading-7 text-[#4B5563]">"Pengalaman trekking yang luar biasa. Air terjun sangat segar dan pemandangannya benar-benar indah."</p>
                        <div class="mt-6 flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-emerald-100 text-sm font-black text-[#007A5A]">{{ strtoupper(substr($name, 0, 1)) }}</div>
                            <div><p class="text-sm font-black">{{ $name }}</p><p class="text-xs text-[#6B7280]">Wisatawan</p></div>
                        </div>
                    </article>
                @endforeach
            @endforelse
        </div>
    </div>
</section>

<section class="app-container py-24">
    <div class="grid overflow-hidden rounded-[28px] bg-white shadow-[0_24px_55px_rgba(15,27,45,.12)] lg:grid-cols-[1.05fr_.95fr]">
        <div class="bg-[#0F1B2D] p-8 text-white md:p-12">
            <h2 class="text-3xl font-black text-[#67FFD0]">Hubungi Kami</h2>
            <div class="mt-8 space-y-6 text-sm text-slate-200">
                <p class="flex gap-3"><x-icon name="map-pin" class="h-5 w-5 text-[#67FFD0]" /> {{ $settings['address'] ?? 'Senaru, Lombok Utara, Nusa Tenggara Barat' }}</p>
                <p class="flex gap-3"><x-icon name="phone" class="h-5 w-5 text-[#67FFD0]" /> {{ $settings['contact_phone'] ?? '+62 812 3456 7890' }}</p>
                <p class="flex gap-3"><x-icon name="mail" class="h-5 w-5 text-[#67FFD0]" /> {{ $settings['contact_email'] ?? 'info@sendanggile.test' }}</p>
            </div>
        </div>
        <div class="flex min-h-[320px] flex-col items-center justify-center bg-[#DDE8FF] p-8 text-center">
            <div class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-100 text-[#007A5A]"><x-icon name="map-pin" class="h-7 w-7" /></div>
            <h3 class="mt-5 text-xl font-black">Jelajahi Lokasi Kami</h3>
            <p class="mt-2 max-w-sm text-sm leading-7 text-[#6B7280]">Temukan rute terbaik menuju Gerbang Wisata Senaru.</p>
            <a href="{{ $settings['google_maps_url'] ?? route('contact') }}" class="btn-primary mt-5">Buka Google Maps</a>
        </div>
    </div>
</section>
@endsection
