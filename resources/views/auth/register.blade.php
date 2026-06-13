@extends('layouts.auth')

@section('title', 'Register Wisatawan')

@section('content')
<div class="text-center">
    <p class="auth-eyebrow">Mulai Perjalanan</p>
    <h1 id="auth-title" class="auth-title">Buat Akun Wisatawan</h1>
    <p class="auth-subtitle">Daftar untuk booking kunjungan dan menyimpan riwayat perjalanan Anda.</p>
</div>

<form method="POST" action="{{ route('register') }}" class="auth-form">
    @csrf
    <div class="auth-field">
        <label class="auth-label" for="name">Nama lengkap</label>
        <div class="relative">
            <span class="auth-field-icon"><x-icon name="user" class="h-5 w-5" /></span>
            <input id="name" name="name" value="{{ old('name') }}" placeholder="Nama Anda" class="auth-input pl-12" required>
        </div>
        @error('name') <p class="auth-error">{{ $message }}</p> @enderror
    </div>

    <div class="auth-field">
        <label class="auth-label" for="email">Email</label>
        <div class="relative">
            <span class="auth-field-icon"><x-icon name="mail" class="h-5 w-5" /></span>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@email.com" class="auth-input pl-12" required>
        </div>
        @error('email') <p class="auth-error">{{ $message }}</p> @enderror
    </div>

    <div class="auth-field">
        <label class="auth-label" for="phone">Nomor WhatsApp</label>
        <div class="relative">
            <span class="auth-field-icon"><x-icon name="phone" class="h-5 w-5" /></span>
            <input id="phone" name="phone" value="{{ old('phone') }}" placeholder="+62 812 3456 7890" class="auth-input pl-12">
        </div>
        @error('phone') <p class="auth-error">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="auth-field">
            <label class="auth-label" for="password">Password</label>
            <div class="relative">
                <span class="auth-field-icon"><x-icon name="lock" class="h-5 w-5" /></span>
                <input id="password" name="password" type="password" placeholder="********" class="auth-input pl-12" required>
            </div>
            @error('password') <p class="auth-error">{{ $message }}</p> @enderror
        </div>
        <div class="auth-field">
            <label class="auth-label" for="password_confirmation">Konfirmasi</label>
            <div class="relative">
                <span class="auth-field-icon"><x-icon name="lock" class="h-5 w-5" /></span>
                <input id="password_confirmation" name="password_confirmation" type="password" placeholder="********" class="auth-input pl-12" required>
            </div>
        </div>
    </div>

    <button class="auth-button" type="submit">Daftar</button>

    <p class="auth-switch">
        Sudah punya akun?
        <a class="auth-link" href="{{ route('login') }}">Masuk Sekarang</a>
    </p>
</form>
@endsection
