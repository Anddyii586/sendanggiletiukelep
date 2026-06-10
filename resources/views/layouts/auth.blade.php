<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Auth') - Sendang Gile</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F7F8FC] text-[#111827] antialiased">
    <main class="grid min-h-screen lg:grid-cols-[1.08fr_.92fr]">
        <section class="auth-image-panel relative hidden overflow-hidden lg:block">
            <div class="absolute inset-x-10 top-10">
                <a href="{{ route('home') }}" class="flex items-center gap-3 text-2xl font-black text-white">
                    <x-icon name="droplet" class="h-8 w-8 text-[#67FFD0]" />
                    Sendang Gile & Tiu Kelep
                </a>
            </div>
            <div class="absolute inset-x-10 bottom-14 max-w-2xl text-white">
                <h1 class="text-5xl font-black leading-tight">Jelajahi Keajaiban Alam Lombok Utara.</h1>
                <p class="mt-5 text-lg leading-8 text-emerald-100">Nikmati ketenangan di air terjun Sendang Gile dan Tiu Kelep yang legendaris. Pengalaman tak terlupakan menanti Anda.</p>
            </div>
        </section>

        <section class="flex min-h-screen items-center justify-center px-5 py-10 sm:px-8">
            <div class="auth-card w-full max-w-[520px]">
                <a href="{{ route('home') }}" class="mb-10 flex items-center gap-2 text-lg font-black text-[#007A5A] lg:hidden">
                    <x-icon name="droplet" class="h-6 w-6" /> Sendang Gile & Tiu Kelep
                </a>
                @include('partials.alerts')
                @yield('content')
                <p class="mt-12 text-center text-xs font-medium text-slate-500">&copy; {{ date('Y') }} Sendang Gile & Tiu Kelep Waterfalls. Senaru, North Lombok.</p>
            </div>
        </section>
    </main>
</body>
</html>
