@extends('layouts.auth')

@section('title', 'Login')

@section('content')
<div class="text-center">
    <p class="auth-eyebrow">Portal Wisata</p>
    <h1 id="auth-title" class="auth-title">Selamat Datang Kembali</h1>
    <p class="auth-subtitle">Masuk untuk melanjutkan rencana perjalanan Anda.</p>
</div>

<form method="POST" action="{{ route('login') }}" class="auth-form">
    @csrf
    <div class="auth-field">
        <label class="auth-label" for="email">Email</label>
        <div class="relative">
            <span class="auth-field-icon"><x-icon name="mail" class="h-5 w-5" /></span>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="nama@email.com" class="auth-input pl-12" required autofocus>
        </div>
        @error('email') <p class="auth-error">{{ $message }}</p> @enderror
    </div>

    <div class="auth-field">
        <label class="auth-label" for="password">Password</label>
        <div class="relative">
            <span class="auth-field-icon"><x-icon name="lock" class="h-5 w-5" /></span>
            <input id="password" name="password" type="password" placeholder="********" class="auth-input pl-12" required>
        </div>
        @error('password') <p class="auth-error">{{ $message }}</p> @enderror
    </div>

    <label class="auth-check">
        <input type="checkbox" name="remember" value="1" class="auth-checkbox">
        Ingat saya
    </label>

    <button class="auth-button" type="submit">Masuk</button>

    <p class="auth-switch">
        Belum punya akun?
        <a class="auth-link" href="{{ route('register') }}">Daftar Sekarang</a>
    </p>
</form>
@endsection
