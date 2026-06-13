@php
    $navOverlay = $navOverlay ?? false;
    $linkBase = 'text-white/75 hover:text-white';
    $active = 'text-[#67FFD0] after:absolute after:left-3 after:right-3 after:-bottom-1 after:h-0.5 after:rounded-full after:bg-[#67FFD0]';
    $mobileLink = 'rounded-[16px] px-4 py-3 text-sm font-bold text-white/82 transition hover:bg-white/10 hover:text-white';
    $mobileActive = 'rounded-[16px] bg-white/10 px-4 py-3 text-sm font-black text-[#67FFD0]';
    $headerClass = $navOverlay
        ? 'absolute inset-x-0 top-0 z-40 pt-4 sm:pt-5'
        : 'sticky top-0 z-40 bg-[#F7F8FC]/80 py-3 backdrop-blur';
    $shellClass = $navOverlay
        ? 'border-white/15 bg-[#0F1B2D]/75 shadow-[0_18px_50px_rgba(0,0,0,0.18)] backdrop-blur-xl'
        : 'border-white/10 bg-[#0F1B2D]/95 shadow-[0_18px_42px_rgba(15,27,45,0.14)]';
    $bookingRoute = route('bookings.create');
@endphp

<header class="{{ $headerClass }}">
    <div class="app-container">
        <div class="{{ $shellClass }} flex max-w-full flex-wrap items-center justify-between gap-3 rounded-[26px] border px-3 py-3 text-white ring-1 ring-white/5 lg:flex-nowrap lg:gap-5 lg:px-5" data-mobile-menu-root>
            <a href="{{ route('home') }}" class="flex min-w-0 flex-1 items-center gap-2 text-sm font-black text-white sm:text-lg lg:flex-none">
                <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#10B981] text-white shadow-[0_10px_22px_rgba(16,185,129,0.25)] lg:h-10 lg:w-10">
                    <x-icon name="droplet" class="h-5 w-5 lg:h-6 lg:w-6" />
                </span>
                <span class="min-w-0 max-w-[180px] truncate sm:max-w-[260px] lg:max-w-none">Sendang Gile & Tiu Kelep</span>
            </a>

            <nav class="hidden min-w-0 items-center justify-center font-bold lg:order-none lg:flex lg:w-auto lg:gap-5 lg:text-sm xl:gap-7">
                <a class="relative rounded-full px-2 py-2 transition {{ request()->routeIs('home') ? $active : $linkBase }}" href="{{ route('home') }}">Beranda</a>
                <a class="relative rounded-full px-2 py-2 transition {{ request()->routeIs('packages.*') ? $active : $linkBase }}" href="{{ route('packages.index') }}">Paket</a>
                <a class="relative rounded-full px-2 py-2 transition {{ request()->routeIs('destination') ? $active : $linkBase }}" href="{{ route('destination') }}">Destinasi</a>
                <a class="relative rounded-full px-2 py-2 transition {{ request()->routeIs('gallery') ? $active : $linkBase }}" href="{{ route('gallery') }}">Galeri</a>
                <a class="relative rounded-full px-2 py-2 transition {{ request()->routeIs('reviews') ? $active : $linkBase }}" href="{{ route('reviews') }}">Review</a>
                <a class="relative rounded-full px-2 py-2 transition {{ request()->routeIs('contact') ? $active : $linkBase }}" href="{{ route('contact') }}">Kontak</a>
            </nav>

            <button
                type="button"
                class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-white/10 text-white ring-1 ring-white/15 transition hover:bg-white/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#67FFD0] lg:hidden"
                aria-label="Buka menu"
                aria-expanded="false"
                data-mobile-menu-toggle
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
                    <path d="M5 7h14M5 12h14M5 17h14" />
                </svg>
            </button>

            <div class="hidden shrink-0 items-center gap-2 sm:gap-3 lg:flex">
                @auth
                    @if(auth()->user()->isAdmin())
                        <a class="rounded-full bg-white/10 px-3 py-2 text-xs font-bold text-white ring-1 ring-white/15 transition hover:bg-white/20 sm:px-4 sm:text-sm" href="{{ route('admin.dashboard') }}">Admin</a>
                    @else
                        <a class="rounded-full bg-white/10 px-3 py-2 text-xs font-bold text-white ring-1 ring-white/15 transition hover:bg-white/20 sm:px-4 sm:text-sm" href="{{ route('my-bookings.index') }}">Pesanan Saya</a>
                    @endif
                    <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                        @csrf
                        <button class="text-sm font-bold text-white/70 transition hover:text-white" type="submit">
                            Keluar
                        </button>
                    </form>
                @else
                    <a class="hidden text-sm font-bold text-white/75 transition hover:text-white sm:inline-flex" href="{{ route('login') }}">Login</a>
                @endauth

                <a class="inline-flex items-center justify-center rounded-full bg-[#10B981] px-3 py-2 text-xs font-black text-[#06251D] shadow-[0_12px_24px_rgba(16,185,129,0.24)] transition hover:bg-[#67FFD0] sm:px-5 sm:py-2.5 sm:text-sm" href="{{ $bookingRoute }}">
                    <span class="hidden sm:inline">Booking Sekarang</span>
                    <span class="sm:hidden">Booking</span>
                </a>
            </div>

            <div class="hidden w-full lg:hidden" data-mobile-menu-panel>
                <div class="mt-1 grid gap-2 border-t border-white/10 pt-3">
                    <a class="{{ request()->routeIs('home') ? $mobileActive : $mobileLink }}" href="{{ route('home') }}">Beranda</a>
                    <a class="{{ request()->routeIs('packages.*') ? $mobileActive : $mobileLink }}" href="{{ route('packages.index') }}">Paket</a>
                    <a class="{{ request()->routeIs('destination') ? $mobileActive : $mobileLink }}" href="{{ route('destination') }}">Destinasi</a>
                    <a class="{{ request()->routeIs('gallery') ? $mobileActive : $mobileLink }}" href="{{ route('gallery') }}">Galeri</a>
                    <a class="{{ request()->routeIs('reviews') ? $mobileActive : $mobileLink }}" href="{{ route('reviews') }}">Review</a>
                    <a class="{{ request()->routeIs('contact') ? $mobileActive : $mobileLink }}" href="{{ route('contact') }}">Kontak</a>

                    @auth
                        @if(auth()->user()->isAdmin())
                            <a class="{{ $mobileLink }}" href="{{ route('admin.dashboard') }}">Admin</a>
                        @else
                            <a class="{{ $mobileLink }}" href="{{ route('my-bookings.index') }}">Pesanan Saya</a>
                        @endif
                    @else
                        <a class="{{ $mobileLink }}" href="{{ route('login') }}">Login</a>
                    @endauth

                    <a class="mt-1 inline-flex items-center justify-center rounded-[18px] bg-[#10B981] px-4 py-3 text-sm font-black text-[#06251D] shadow-[0_12px_24px_rgba(16,185,129,0.2)] transition hover:bg-[#67FFD0]" href="{{ $bookingRoute }}">
                        Booking Sekarang
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>
