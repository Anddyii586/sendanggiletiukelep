@extends('layouts.app')

@section('title', 'Review Publik')

@section('content')
<section class="app-container py-12">
    <div class="mb-10 flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="eyebrow">Review Publik</p>
            <h1 class="mt-4 section-title">Apa Kata Mereka?</h1>
            <div class="mt-4 flex items-center gap-3">
                <x-rating-stars rating="5" />
                <strong class="text-xl">4.9 / 5.0</strong>
                <span class="text-sm text-[#6B7280]">Berdasarkan review wisatawan</span>
            </div>
        </div>
        <a href="{{ auth()->check() && auth()->user()->role === 'user' ? route('my-bookings.index') : route('login') }}" class="btn-primary">Tulis Review</a>
    </div>

    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
        @forelse($reviews as $review)
            <article class="surface-card p-6">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100 text-sm font-black text-[#007A5A]">{{ strtoupper(substr($review->user->name, 0, 1)) }}</div>
                        <div>
                            <p class="font-black">{{ $review->user->name }}</p>
                            <p class="text-xs text-[#6B7280]">{{ optional($review->created_at)->format('d M Y') }}</p>
                        </div>
                    </div>
                    <x-rating-stars :rating="$review->rating" class="h-4 w-4 text-[#007A5A]" />
                </div>
                <p class="mt-5 text-sm leading-7 text-[#6B7280]">{{ $review->comment }}</p>
                <p class="mt-5 text-xs font-bold uppercase tracking-wider text-[#007A5A]">{{ $review->booking->service->name ?? 'Layanan wisata' }}</p>
            </article>
        @empty
            <div class="surface-card p-8 text-[#6B7280] md:col-span-2 xl:col-span-3">Review publik belum tersedia.</div>
        @endforelse
    </div>

    <div class="mt-8">{{ $reviews->links() }}</div>
</section>
@endsection
