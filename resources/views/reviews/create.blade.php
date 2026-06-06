@extends('layouts.traveler')

@section('title', 'Buat Review')

@section('content')
<section class="max-w-4xl">
    <div class="mb-8">
        <p class="eyebrow">Review</p>
        <h1 class="mt-3 text-4xl font-black tracking-tight">Bagikan ulasan kunjungan</h1>
        <p class="mt-2 text-[#6B7280]">{{ $booking->service->name }} - {{ $booking->visit_date->format('d M Y') }}</p>
    </div>

    <form method="POST" action="{{ route('reviews.store', $booking) }}" class="surface-card p-7">
        @csrf
        <div class="space-y-6">
            <div>
                <label class="form-label" for="rating">Rating</label>
                <div class="grid gap-3 sm:grid-cols-5">
                    @for($i = 5; $i >= 1; $i--)
                        <label class="flex cursor-pointer items-center justify-center gap-2 rounded-[16px] border border-[#E5EAF2] bg-white px-4 py-3 font-black hover:border-[#007A5A]">
                            <input type="radio" name="rating" value="{{ $i }}" class="sr-only peer" @checked(old('rating', 5) == $i)>
                            <span class="peer-checked:text-[#007A5A]">{{ $i }}</span>
                            <x-icon name="star" class="h-4 w-4 text-[#007A5A]" />
                        </label>
                    @endfor
                </div>
                @error('rating') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="form-label" for="comment">Komentar</label>
                <textarea id="comment" name="comment" rows="6" class="form-input" placeholder="Ceritakan pengalaman Anda..." required>{{ old('comment') }}</textarea>
                @error('comment') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
            </div>
            <button class="btn-primary w-full py-4" type="submit">Kirim Review</button>
        </div>
    </form>
</section>
@endsection
