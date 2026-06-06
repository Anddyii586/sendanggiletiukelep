@extends('layouts.app')

@section('title', 'Kontak Pengelola')

@section('content')
<section class="topographic-bg py-20">
    <div class="app-container">
        <div class="max-w-lg rounded-[28px] bg-white p-8 shadow-[0_26px_56px_rgba(15,27,45,.18)] md:p-10">
            <p class="eyebrow">Kontak</p>
            <h1 class="mt-4 text-4xl font-black tracking-tight text-[#007A5A]">Hubungi Kami</h1>
            <p class="mt-4 text-sm leading-7 text-[#6B7280]">Tim pengelola siap membantu informasi kunjungan, booking, dan layanan guide lokal.</p>
            <div class="mt-8 space-y-5">
                <div class="flex gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-[#007A5A]"><x-icon name="map-pin" class="h-6 w-6" /></span>
                    <div><p class="text-sm font-black">Alamat</p><p class="mt-1 text-sm leading-6 text-[#6B7280]">{{ $settings['address'] ?? '-' }}</p></div>
                </div>
                <div class="flex gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-[#007A5A]"><x-icon name="phone" class="h-6 w-6" /></span>
                    <div><p class="text-sm font-black">Telepon / WhatsApp</p><p class="mt-1 text-sm leading-6 text-[#6B7280]">{{ $settings['contact_phone'] ?? '-' }}</p></div>
                </div>
                <div class="flex gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-[#007A5A]"><x-icon name="mail" class="h-6 w-6" /></span>
                    <div><p class="text-sm font-black">Email</p><p class="mt-1 text-sm leading-6 text-[#6B7280]">{{ $settings['contact_email'] ?? '-' }}</p></div>
                </div>
                <div class="flex gap-4">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-[#007A5A]"><x-icon name="clock" class="h-6 w-6" /></span>
                    <div><p class="text-sm font-black">Jam Layanan</p><p class="mt-1 text-sm leading-6 text-[#6B7280]">{{ $settings['opening_hours'] ?? '-' }}</p></div>
                </div>
            </div>
            <a href="{{ $settings['google_maps_url'] ?? '#' }}" class="btn-primary mt-8">Buka Google Maps</a>
        </div>
    </div>
</section>
@endsection
