@extends('layouts.app')

@section('title', $service->name.' - Sendang Gile & Tiu Kelep')

@section('content')
<section class="app-container py-10 lg:py-14">
    <div class="grid gap-8 lg:grid-cols-[1fr_380px]">
        <div class="space-y-8">
            <div class="surface-card overflow-hidden">
                <img src="{{ asset($service->image_path ?: 'assets/images/destination-waterfall.jpg') }}" alt="Paket wisata {{ $service->name }}" loading="eager" decoding="async" class="h-[360px] w-full object-cover lg:h-[520px]">
                <div class="p-6 sm:p-8">
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="rounded-full bg-emerald-100 px-4 py-2 text-xs font-black uppercase text-[#007A5A]">Senaru, Lombok Utara</span>
                        <span class="rounded-full bg-[#EEF3FF] px-4 py-2 text-xs font-black uppercase text-[#4B5563]">{{ $service->pricing_type === 'per_trip' ? 'Per Trip' : 'Per Peserta' }}</span>
                    </div>
                    <h1 class="mt-5 text-4xl font-black tracking-tight md:text-5xl">{{ $service->name }}</h1>
                    <p class="mt-5 max-w-3xl text-base leading-8 text-[#6B7280]">{{ $service->description }}</p>
                </div>
            </div>

            <div class="grid gap-5 md:grid-cols-3">
                @foreach([
                    ['Guide lokal', 'Pemandu profesional dari area Senaru.', 'guide'],
                    ['E-ticket', 'Voucher otomatis setelah payment paid.', 'ticket'],
                    ['Checkout aman', 'Pembayaran via Midtrans Snap.', 'receipt'],
                ] as [$title, $text, $icon])
                    <article class="soft-card p-6">
                        <x-icon :name="$icon" class="h-7 w-7 text-[#007A5A]" />
                        <h2 class="mt-5 text-lg font-black">{{ $title }}</h2>
                        <p class="mt-3 text-sm leading-6 text-[#6B7280]">{{ $text }}</p>
                    </article>
                @endforeach
            </div>

            <div class="surface-card p-6 sm:p-8">
                <h2 class="text-2xl font-black">Ketentuan paket</h2>
                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    @foreach([
                        'Tanggal kunjungan tidak boleh sebelum hari ini.',
                        'Harga dihitung otomatis oleh sistem dari data paket.',
                        'Pembayaran resmi diproses lewat Midtrans Snap.',
                        'E-ticket tersedia setelah status pembayaran paid.',
                    ] as $rule)
                        <div class="flex gap-3 rounded-[18px] bg-[#EEF3FF] p-4">
                            <x-icon name="check" class="h-5 w-5 flex-shrink-0 text-[#007A5A]" />
                            <p class="text-sm font-bold leading-6 text-[#374151]">{{ $rule }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="surface-card p-6 sm:p-8">
                <h2 class="text-2xl font-black">Review paket ini</h2>
                <div class="mt-6 grid gap-5 md:grid-cols-3">
                    @forelse($reviews as $review)
                        <article class="rounded-[18px] border border-[#E5EAF2] p-5">
                            <x-rating-stars :rating="$review->rating" />
                            @if($review->comment)
                                <p class="mt-4 text-sm leading-7 text-[#6B7280]">{{ $review->comment }}</p>
                            @endif
                            <p class="mt-5 text-sm font-black">{{ $review->user->name }}</p>
                        </article>
                    @empty
                        <p class="rounded-[18px] bg-[#F7F8FC] p-5 text-sm leading-7 text-[#6B7280] md:col-span-3">Belum ada review untuk paket ini.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <aside class="surface-card h-fit p-6 sm:p-7 lg:sticky lg:top-28">
            <p class="eyebrow">Booking Paket</p>
            <h2 class="mt-2 text-2xl font-black">Siap berpetualang?</h2>
            <div class="mt-6 rounded-[20px] bg-[#EEF3FF] p-5">
                <p class="text-sm font-bold text-[#6B7280]">Harga mulai</p>
                <p class="mt-2 text-4xl font-black text-[#007A5A]">Rp {{ number_format($service->price, 0, ',', '.') }}</p>
                <p class="mt-1 text-sm font-bold text-[#6B7280]">/{{ $service->pricing_type === 'per_trip' ? 'trip' : 'orang' }}</p>
            </div>
            <dl class="mt-6 grid gap-4 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-[#6B7280]">Status paket</dt><dd class="font-black">{{ $service->is_active ? 'Aktif' : 'Nonaktif' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-[#6B7280]">Pembayaran</dt><dd class="font-black">Midtrans Snap</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-[#6B7280]">Voucher</dt><dd class="font-black">E-ticket</dd></div>
            </dl>
            <form method="GET" action="{{ route('bookings.create') }}" class="mt-7 space-y-4">
                <input type="hidden" name="package" value="{{ $service->id }}">
                <div>
                    <label class="form-label" for="visit_date">Tanggal kunjungan</label>
                    <input id="visit_date" name="visit_date" type="date" min="{{ now()->toDateString() }}" class="form-input">
                </div>
                <div>
                    <label class="form-label" for="participant_count">Jumlah peserta</label>
                    <input id="participant_count" name="participant_count" type="number" min="1" max="100" value="1" class="form-input">
                </div>
                <button class="btn-primary w-full py-4" type="submit">Pesan Sekarang</button>
            </form>
            <a href="{{ route('destination') }}" class="btn-secondary mt-3 w-full">Lihat Destinasi</a>
        </aside>
    </div>
</section>
@endsection
