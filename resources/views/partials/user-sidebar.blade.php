@php
    $reviewCount = auth()->user()?->reviews()->count() ?? 0;
@endphp

<aside class="dashboard-sidebar">
    <div class="px-6 py-7">
        <h1 class="text-2xl font-black leading-tight text-[#67FFD0]">Traveler<br>Dashboard</h1>
        <p class="mt-1 text-sm font-semibold text-slate-300">Welcome back</p>
    </div>

    <nav class="flex gap-2 overflow-x-auto px-3 pb-4 lg:block lg:px-0">
        <a href="{{ route('dashboard') }}" class="dash-nav-link {{ request()->routeIs('dashboard') ? 'dash-nav-link-active' : '' }}">
            <x-icon name="home" class="h-5 w-5" /> Home
        </a>
        <a href="{{ route('bookings.index') }}" class="dash-nav-link {{ request()->routeIs('bookings.*') || request()->routeIs('payments.*') ? 'dash-nav-link-active' : '' }}">
            <x-icon name="history" class="h-5 w-5" /> Riwayat Booking
        </a>
        <a href="{{ route('reviews') }}" class="dash-nav-link">
            <x-icon name="star" class="h-5 w-5" /> My Reviews
        </a>
        <a href="{{ route('dashboard') }}#profile" class="dash-nav-link">
            <x-icon name="user" class="h-5 w-5" /> Profile
        </a>
    </nav>

    <div class="border-t border-white/10 p-6 lg:absolute lg:bottom-0 lg:left-0 lg:right-0">
        <div id="profile" class="flex items-center gap-3">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-[#DDFBF0] text-sm font-black text-[#007A5A] ring-2 ring-[#67FFD0]">
                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="truncate text-sm font-black text-white">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-300">Member sejak {{ optional(auth()->user()->created_at)->format('Y') }} | {{ $reviewCount }} review</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="mt-7">
            @csrf
            <button type="submit" class="flex items-center gap-3 text-sm font-black text-red-400 transition hover:text-red-300">
                <x-icon name="logout" class="h-5 w-5" /> Logout
            </button>
        </form>
    </div>
</aside>
