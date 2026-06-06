@extends('layouts.auth')

@section('title', 'Register Wisatawan')

@section('content')
<div>
    <h1 class="text-4xl font-black tracking-tight">Buat Akun Wisatawan</h1>
    <p class="mt-3 text-base text-[#6B7280]">Daftar untuk booking kunjungan dan menyimpan riwayat perjalanan Anda.</p>
</div>

<form method="POST" action="{{ route('register') }}" class="mt-10 space-y-5">
    @csrf
    <div>
        <label class="form-label" for="name">Nama lengkap</label>
        <div class="relative">
            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="user" class="h-5 w-5" /></span>
            <input id="name" name="name" value="{{ old('name') }}" placeholder="Nama Anda" class="form-input pl-12" required>
        </div>
        @error('name') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label" for="email">Email</label>
        <div class="relative">
            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="mail" class="h-5 w-5" /></span>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@email.com" class="form-input pl-12" required>
        </div>
        @error('email') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <label class="form-label" for="phone">Nomor WhatsApp</label>
        <div class="relative">
            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="phone" class="h-5 w-5" /></span>
            <input id="phone" name="phone" value="{{ old('phone') }}" placeholder="+62 812 3456 7890" class="form-input pl-12">
        </div>
        @error('phone') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label class="form-label" for="password">Password</label>
            <div class="relative">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="lock" class="h-5 w-5" /></span>
                <input id="password" name="password" type="password" placeholder="********" class="form-input pl-12" required>
            </div>
            @error('password') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="form-label" for="password_confirmation">Konfirmasi</label>
            <div class="relative">
                <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="lock" class="h-5 w-5" /></span>
                <input id="password_confirmation" name="password_confirmation" type="password" placeholder="********" class="form-input pl-12" required>
            </div>
        </div>
    </div>

    <button class="btn-primary w-full py-4 text-base" type="submit">Daftar Sekarang <span aria-hidden="true">-></span></button>

    <p class="pt-2 text-center text-sm font-medium text-[#6B7280]">
        Sudah punya akun?
        <a class="font-black text-[#007A5A] hover:text-[#005f46]" href="{{ route('login') }}">Masuk</a>
    </p>
</form>
@endsection
