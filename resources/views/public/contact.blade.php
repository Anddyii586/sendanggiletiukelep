@extends('layouts.app')

@section('title', 'Kontak Pengelola')

@section('content')
@php
    $address = $settings['address'] ?? 'Senaru, Lombok Utara, Nusa Tenggara Barat';
    $phone = $settings['contact_phone'] ?? null;
    $email = $settings['contact_email'] ?? null;
    $hours = $settings['opening_hours'] ?? '07.00 - 17.00 WITA';
    $mapsUrl = $settings['google_maps_url'] ?? '#';
    $phoneDigits = $phone ? preg_replace('/\D+/', '', $phone) : null;
    $whatsappNumber = $phoneDigits
        ? (str_starts_with($phoneDigits, '62') ? $phoneDigits : '62' . ltrim($phoneDigits, '0'))
        : null;
    $whatsappUrl = $whatsappNumber ? 'https://wa.me/' . $whatsappNumber : null;
@endphp

<section class="relative overflow-hidden bg-[#EDF4F7] py-16 sm:py-20 lg:py-24">
    <div class="absolute inset-x-0 top-0 h-56 bg-gradient-to-b from-white to-transparent"></div>

    <div class="app-container relative">
        <div class="grid min-w-0 items-stretch gap-6 lg:grid-cols-[1.02fr_.98fr] lg:gap-8">
            <div class="flex min-h-[520px] min-w-0 flex-col justify-between rounded-[30px] bg-[#0F1B2D] p-7 text-white shadow-[0_28px_65px_rgba(15,27,45,.18)] sm:p-10 lg:p-12">
                <div class="min-w-0">
                    <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#67FFD0]">Kontak</p>
                    <h1 class="mt-5 max-w-2xl text-4xl font-black leading-tight tracking-tight sm:text-5xl">
                        Hubungi pengelola wisata Senaru.
                    </h1>
                    <p class="mt-5 max-w-xl break-words text-sm leading-7 text-slate-300 sm:text-base sm:leading-8">
                        Tim lokal siap membantu informasi kunjungan, rute menuju air terjun, layanan guide, dan kebutuhan perjalanan Anda.
                    </p>
                </div>

                <div class="mt-10 grid min-w-0 gap-3 sm:grid-cols-2">
                    <div class="min-w-0 rounded-[22px] border border-white/10 bg-white/10 p-5">
                        <x-icon name="clock" class="h-6 w-6 text-[#67FFD0]" />
                        <p class="mt-4 text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Jam Layanan</p>
                        <p class="mt-2 text-sm font-black text-white">{{ $hours }}</p>
                    </div>
                    <div class="min-w-0 rounded-[22px] border border-white/10 bg-white/10 p-5">
                        <x-icon name="map-pin" class="h-6 w-6 text-[#67FFD0]" />
                        <p class="mt-4 text-xs font-bold uppercase tracking-[0.14em] text-slate-400">Area Wisata</p>
                        <p class="mt-2 text-sm font-black text-white">Sendang Gile & Tiu Kelep</p>
                    </div>
                </div>
            </div>

            <div class="surface-card flex min-w-0 flex-col p-6 sm:p-8 lg:p-9">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <p class="eyebrow">Info Pengelola</p>
                        <h2 class="mt-3 text-2xl font-black tracking-tight text-[#111827] sm:text-3xl">Kontak Resmi</h2>
                    </div>
                    <span class="hidden h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-[#007A5A] sm:flex">
                        <x-icon name="phone" class="h-6 w-6" />
                    </span>
                </div>

                <div class="mt-7 space-y-4">
                    <article class="min-w-0 rounded-[22px] border border-[#E5EAF2] bg-[#F8FAFC] p-5">
                        <div class="flex gap-4">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-white text-[#007A5A] shadow-[0_10px_24px_rgba(15,27,45,.06)]">
                                <x-icon name="map-pin" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-black text-[#111827]">Alamat</p>
                                <p class="mt-1 text-sm leading-6 text-[#6B7280]">{{ $address }}</p>
                            </div>
                        </div>
                    </article>

                    <article class="min-w-0 rounded-[22px] border border-[#E5EAF2] bg-white p-5">
                        <div class="flex gap-4">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-[#007A5A]">
                                <x-icon name="phone" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-black text-[#111827]">Telepon / WhatsApp</p>
                                <p class="mt-1 text-sm leading-6 text-[#6B7280]">{{ $phone ?? '-' }}</p>
                            </div>
                        </div>
                    </article>

                    <article class="min-w-0 rounded-[22px] border border-[#E5EAF2] bg-white p-5">
                        <div class="flex gap-4">
                            <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-emerald-50 text-[#007A5A]">
                                <x-icon name="mail" class="h-5 w-5" />
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-black text-[#111827]">Email</p>
                                <p class="mt-1 break-words text-sm leading-6 text-[#6B7280]">{{ $email ?? '-' }}</p>
                            </div>
                        </div>
                    </article>
                </div>

                <div class="mt-7 grid gap-3 sm:grid-cols-2">
                    <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" class="btn-primary">
                        <x-icon name="map-pin" class="h-5 w-5" />
                        Buka Google Maps
                    </a>
                    @if($whatsappUrl)
                        <a href="{{ $whatsappUrl }}" target="_blank" rel="noopener noreferrer" class="btn-secondary">
                            <x-icon name="whatsapp" class="h-5 w-5" />
                            WhatsApp
                        </a>
                    @else
                        <a href="{{ route('packages.index') }}" class="btn-secondary">
                            <x-icon name="ticket" class="h-5 w-5" />
                            Lihat Paket
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="mt-8 grid min-w-0 gap-6 lg:grid-cols-[.9fr_1.1fr]">
            <div class="min-w-0 rounded-[26px] border border-[#DDE6F0] bg-white/80 p-6 shadow-[0_18px_44px_rgba(15,27,45,.08)] backdrop-blur">
                <p class="eyebrow">Rute</p>
                <h2 class="mt-3 text-2xl font-black tracking-tight text-[#111827]">Menuju titik awal kunjungan</h2>
                <p class="mt-3 text-sm leading-7 text-[#6B7280]">
                    Gunakan tautan peta untuk membuka navigasi langsung ke kawasan wisata. Untuk rombongan atau kebutuhan guide, hubungi pengelola sebelum datang.
                </p>
            </div>

            <div class="relative min-h-[260px] min-w-0 overflow-hidden rounded-[26px] bg-[#DDE6F0] p-6 shadow-[0_18px_44px_rgba(15,27,45,.08)]">
                <div class="absolute inset-0 opacity-70 topographic-bg"></div>
                <div class="relative flex h-full min-h-[220px] flex-col items-center justify-center rounded-[22px] border border-white/60 bg-white/70 p-8 text-center backdrop-blur">
                    <span class="flex h-14 w-14 items-center justify-center rounded-full bg-[#0F1B2D] text-[#67FFD0] shadow-[0_16px_34px_rgba(15,27,45,.18)]">
                        <x-icon name="map-pin" class="h-7 w-7" />
                    </span>
                    <h3 class="mt-5 text-xl font-black text-[#111827]">Senaru, Lombok Utara</h3>
                    <p class="mt-2 max-w-md text-sm leading-7 text-[#6B7280]">{{ $address }}</p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
