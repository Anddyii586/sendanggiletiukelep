@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div>
    <h1 class="text-4xl font-black tracking-tight">Selamat Datang Kembali</h1>
    <p class="mt-3 text-base text-[#6B7280]">Masuk untuk melanjutkan rencana perjalanan Anda.</p>
</div>

<form method="POST" action="{{ route('login') }}" class="mt-10 space-y-6">
    @csrf
    <div>
        <label class="form-label" for="email">Email</label>
        <div class="relative">
            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="mail" class="h-5 w-5" /></span>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@email.com" class="form-input pl-12" required autofocus>
        </div>
        @error('email') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
    </div>

    <div>
        <div class="mb-2 flex items-center justify-between gap-3">
            <label class="text-sm font-bold text-[#374151]" for="password">Password</label>
        </div>
        <div class="relative">
            <span class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="lock" class="h-5 w-5" /></span>
            <input id="password" name="password" type="password" placeholder="********" class="form-input pl-12" required>
        </div>
        @error('password') <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p> @enderror
    </div>

    <label class="flex items-center gap-3 text-sm font-semibold text-[#6B7280]">
        <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-[#007A5A] focus:ring-[#007A5A]">
        Ingat saya
    </label>

    <button class="btn-primary w-full py-4 text-base" type="submit">Masuk Ke Akun <span aria-hidden="true">-></span></button>

    <p class="pt-2 text-center text-sm font-medium text-[#6B7280]">
        Belum punya akun?
        <a class="font-black text-[#007A5A] hover:text-[#005f46]" href="{{ route('register') }}">Daftar Sekarang</a>
    </p>
</form>
@endsection
