@extends('layouts.app')

@section('title', 'Galeri Foto')

@section('content')
@php
    $fallbacks = [
        'assets/images/gallery-1.jpg',
        'assets/images/gallery-2.jpg',
        'assets/images/gallery-3.jpg',
        'assets/images/gallery-4.jpg',
        'assets/images/gallery-5.jpg',
    ];
@endphp

<section class="app-container py-12">
    <div class="mx-auto max-w-2xl text-center">
        <p class="eyebrow">Galeri Destinasi</p>
        <h1 class="mt-4 section-title">Keindahan Sendang Gile & Tiu Kelep</h1>
        <p class="mt-4 text-sm leading-7 text-[#6B7280]">Dokumentasi suasana air terjun, jalur hutan, dan panorama Senaru.</p>
    </div>

    <div class="mt-12 grid auto-rows-[220px] gap-5 md:grid-cols-4">
        @forelse($galleries as $gallery)
            @php
                $fallback = $fallbacks[$loop->index % count($fallbacks)];
                $imageUrl = $gallery->image_path && !\Illuminate\Support\Str::startsWith($gallery->image_path, ['http://', 'https://'])
                    ? \Illuminate\Support\Facades\Storage::url($gallery->image_path)
                    : asset($fallback);
            @endphp
            <article class="{{ $loop->first ? 'md:col-span-2 md:row-span-2' : '' }} overflow-hidden rounded-[22px] bg-white shadow-[0_18px_38px_rgba(15,27,45,.08)]">
                <img src="{{ $imageUrl }}" alt="{{ $gallery->title }}" loading="lazy" decoding="async" class="h-full w-full object-cover">
            </article>
        @empty
            @foreach($fallbacks as $fallback)
                <img src="{{ asset($fallback) }}" alt="Galeri wisata" loading="lazy" decoding="async" class="{{ $loop->first ? 'md:col-span-2 md:row-span-2' : '' }} h-full w-full rounded-[22px] object-cover shadow-[0_18px_38px_rgba(15,27,45,.08)]">
            @endforeach
        @endforelse
    </div>

    <div class="mt-8">{{ $galleries->links() }}</div>
</section>
@endsection
