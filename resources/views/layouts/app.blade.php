<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sendang Gile & Tiu Kelep')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
@php
    $navOverlay = trim($__env->yieldContent('nav_variant')) === 'overlay';
@endphp
<body class="min-h-screen bg-[#F7F8FC] text-[#111827] antialiased">
    @include('partials.public-navbar', ['navOverlay' => $navOverlay])

    <main class="{{ $navOverlay ? '' : 'pt-0' }}">
        @unless($navOverlay)
            <div class="app-container pt-6">
                @include('partials.alerts')
            </div>
        @endunless
        @yield('content')
    </main>

    @include('partials.public-footer')
</body>
</html>
