<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Auth') - Sendang Gile</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen overflow-x-hidden bg-[#071D1A] text-white antialiased">
    <main class="auth-shell">
        <div class="auth-stack">
            <section class="auth-card" aria-labelledby="auth-title">
                <a href="{{ route('home') }}" class="auth-brand" aria-label="Kembali ke beranda Sendang Gile dan Tiu Kelep">
                    <span class="auth-brand-mark">
                        <x-icon name="droplet" class="h-5 w-5" />
                    </span>
                    <span>Sendang Gile &amp; Tiu Kelep</span>
                </a>

                @include('partials.alerts')
                @yield('content')
            </section>

            <p class="auth-footer">&copy; {{ date('Y') }} Sendang Gile &amp; Tiu Kelep Waterfalls. Senaru, North Lombok.</p>
        </div>
    </main>
</body>
</html>
