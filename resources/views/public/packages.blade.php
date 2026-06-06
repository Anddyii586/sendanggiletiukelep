@extends('layouts.app')

@section('title', 'Paket Wisata')

@section('content')
<section class="app-container py-10 lg:py-16">
    <div class="mb-10 flex flex-wrap items-end justify-between gap-5">
        <div>
            <p class="eyebrow">Public Catalog</p>
            <h1 class="mt-3 text-4xl font-black tracking-tight md:text-5xl">Paket Wisata</h1>
            <p class="mt-4 max-w-2xl text-base leading-8 text-[#6B7280]">Pilih paket resmi untuk kunjungan ke Sendang Gile & Tiu Kelep. Booking wajib login dan pembayaran diproses via Midtrans.</p>
        </div>
        <a href="{{ route('gallery') }}" class="btn-secondary">Lihat Galeri</a>
    </div>

    <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
        @forelse($services as $service)
            <article class="surface-card overflow-hidden">
                <img src="{{ asset($service->image_path ?: 'assets/images/gallery-1.jpg') }}" alt="Paket {{ $service->name }}" loading="lazy" decoding="async" class="h-56 w-full object-cover">
                <div class="p-6">
                    <div class="flex items-start justify-between gap-4">
                        <h2 class="text-xl font-black">{{ $service->name }}</h2>
                        @if($service->is_featured)
                            <span class="rounded-full bg-[#10B981] px-3 py-1 text-[10px] font-black uppercase text-white">Populer</span>
                        @endif
                    </div>
                    <p class="mt-4 line-clamp-3 text-sm leading-7 text-[#6B7280]">{{ $service->description }}</p>
                    <div class="mt-6 flex items-end justify-between gap-4">
                        <div>
                            <p class="text-xs font-black uppercase tracking-[0.14em] text-[#6B7280]">Mulai dari</p>
                            <p class="mt-1 text-2xl font-black text-[#007A5A]">Rp {{ number_format($service->price, 0, ',', '.') }}</p>
                        </div>
                        <span class="rounded-full bg-[#EEF3FF] px-3 py-2 text-xs font-black text-[#4B5563]">{{ $service->pricing_type === 'per_trip' ? 'Per trip' : 'Per peserta' }}</span>
                    </div>
                    <div class="mt-6 flex gap-3">
                        <a href="{{ route('packages.show', $service) }}" class="btn-secondary flex-1">Detail</a>
                        <a href="{{ route('bookings.create', ['package' => $service->id]) }}" class="btn-primary flex-1">Pesan</a>
                    </div>
                </div>
            </article>
        @empty
            <div class="surface-card p-8 text-center text-[#6B7280] md:col-span-2 xl:col-span-3">Paket wisata belum tersedia.</div>
        @endforelse
    </div>

    <div class="mt-10">{{ $services->links() }}</div>
</section>
@endsection
