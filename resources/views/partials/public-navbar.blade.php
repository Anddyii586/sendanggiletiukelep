@php
    $navOverlay = $navOverlay ?? false;
    $linkBase = $navOverlay ? 'text-white/80 hover:text-white' : 'text-slate-600 hover:text-[#007A5A]';
    $active = $navOverlay ? 'text-white' : 'text-[#007A5A] after:absolute after:left-3 after:right-3 after:-bottom-1 after:h-0.5 after:rounded-full after:bg-[#007A5A]';
    $bookingRoute = route('bookings.create');
@endphp

<header class="{{ $navOverlay ? 'absolute inset-x-0 top-0 z-40' : 'sticky top-0 z-40 border-b border-[#E5EAF2] bg-white/95 shadow-[0_12px_35px_rgba(15,27,45,0.05)] backdrop-blur' }}">
    <div class="app-container flex flex-wrap items-center justify-between gap-3 py-4 lg:flex-nowrap lg:gap-5 lg:py-5">
        <a href="{{ route('home') }}" class="flex min-w-0 items-center gap-2 text-base font-black sm:text-lg {{ $navOverlay ? 'text-white' : 'text-[#007A5A]' }}">
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-[#10B981] text-white lg:h-10 lg:w-10">
                <x-icon name="droplet" class="h-5 w-5 lg:h-6 lg:w-6" />
            </span>
            <span class="max-w-[210px] truncate sm:max-w-none">Sendang Gile & Tiu Kelep</span>
        </a>

        <nav class="order-3 flex w-full items-center justify-start gap-4 overflow-x-auto pb-1 text-xs font-bold sm:justify-center sm:gap-5 lg:order-none lg:w-auto lg:overflow-visible lg:pb-0 lg:text-sm xl:gap-8">
            <a class="relative px-1 py-2 {{ request()->routeIs('home') ? $active : $linkBase }}" href="{{ route('home') }}">Beranda</a>
            <a class="relative px-1 py-2 {{ request()->routeIs('packages.*') ? $active : $linkBase }}" href="{{ route('packages.index') }}">Paket</a>
            <a class="relative px-1 py-2 {{ request()->routeIs('destination') ? $active : $linkBase }}" href="{{ route('destination') }}">Destinasi</a>
            <a class="relative px-1 py-2 {{ request()->routeIs('gallery') ? $active : $linkBase }}" href="{{ route('gallery') }}">Galeri</a>
            <a class="relative px-1 py-2 {{ request()->routeIs('reviews') ? $active : $linkBase }}" href="{{ route('reviews') }}">Review</a>
            <a class="relative px-1 py-2 {{ request()->routeIs('contact') ? $active : $linkBase }}" href="{{ route('contact') }}">Kontak</a>
        </nav>

        <div class="flex shrink-0 items-center gap-2 sm:gap-3">
            @auth
                @if(auth()->user()->isAdmin())
                    <a class="{{ $navOverlay ? 'rounded-full bg-white/15 px-3 py-2 text-xs font-bold text-white ring-1 ring-white/20 hover:bg-white/25 sm:px-4 sm:text-sm' : 'hidden text-sm font-bold text-slate-600 hover:text-[#007A5A] sm:inline-flex' }}" href="{{ route('admin.dashboard') }}">Admin</a>
                @else
                    <a class="{{ $navOverlay ? 'rounded-full bg-white/15 px-3 py-2 text-xs font-bold text-white ring-1 ring-white/20 hover:bg-white/25 sm:px-4 sm:text-sm' : 'hidden text-sm font-bold text-slate-600 hover:text-[#007A5A] sm:inline-flex' }}" href="{{ route('my-bookings.index') }}">Pesanan Saya</a>
                @endif
                <form method="POST" action="{{ route('logout') }}" class="hidden sm:block">
                    @csrf
                    <button class="{{ $navOverlay ? 'text-sm font-bold text-white/75 hover:text-white' : 'text-sm font-bold text-slate-500 hover:text-red-600' }}" type="submit">
                        Keluar
                    </button>
                </form>
            @else
                <a class="{{ $navOverlay ? 'hidden text-sm font-bold text-white/80 hover:text-white sm:inline-flex' : 'hidden text-sm font-bold text-slate-600 hover:text-[#007A5A] sm:inline-flex' }}" href="{{ route('login') }}">Login</a>
            @endauth

            <a class="inline-flex items-center justify-center rounded-full bg-[#007A5A] px-3 py-2 text-xs font-black text-white shadow-[0_12px_24px_rgba(0,122,90,0.24)] transition hover:bg-[#00684d] sm:px-5 sm:py-2.5 sm:text-sm" href="{{ $bookingRoute }}">
                <span class="hidden sm:inline">Booking Sekarang</span>
                <span class="sm:hidden">Booking</span>
            </a>
        </div>
    </div>
</header>
