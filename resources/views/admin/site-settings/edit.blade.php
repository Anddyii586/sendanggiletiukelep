@extends('layouts.admin')

@section('title', 'Site Settings')

@section('content')
<section>
    <div class="mb-8">
        <p class="eyebrow">Settings</p>
        <h1 class="mt-3 text-4xl font-black tracking-tight">Site Settings</h1>
        <p class="mt-2 text-[#6B7280]">Kelola informasi publik seperti kontak, lokasi, deskripsi destinasi, dan link maps.</p>
    </div>

    <form method="POST" action="{{ route('admin.site-settings.update') }}" class="surface-card p-6 sm:p-8">
        @csrf
        @method('PUT')

        <div class="grid gap-6 lg:grid-cols-2">
            @foreach($fields as $key => $label)
                <div class="{{ str_contains($key, 'description') || str_contains($key, 'facilities') ? 'lg:col-span-2' : '' }}">
                    <label class="form-label" for="{{ $key }}">{{ $label }}</label>
                    @if(str_contains($key, 'description') || str_contains($key, 'facilities'))
                        <textarea id="{{ $key }}" name="{{ $key }}" rows="5" class="form-input">{{ old($key, $settings[$key] ?? '') }}</textarea>
                    @else
                        <input id="{{ $key }}" name="{{ $key }}" value="{{ old($key, $settings[$key] ?? '') }}" class="form-input">
                    @endif
                    @error($key) <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>
            @endforeach
        </div>

        <div class="mt-8 flex flex-wrap gap-3">
            <button class="btn-primary" type="submit">Simpan Settings</button>
            <a href="{{ route('admin.dashboard') }}" class="btn-secondary">Kembali</a>
        </div>
    </form>
</section>
@endsection
