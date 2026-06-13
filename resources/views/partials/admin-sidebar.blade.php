<aside id="admin-sidebar" class="dashboard-sidebar lg:[transform:translateX(0)]" aria-label="Admin navigation" data-admin-sidebar>
    <div class="flex items-center justify-between border-b border-white/10 px-5 py-4 lg:hidden">
        <div>
            <p class="text-xs font-black uppercase tracking-[0.16em] text-[#67FFD0]">Menu Admin</p>
            <p class="mt-1 text-sm font-bold text-slate-200">Waterfalls Management</p>
        </div>
        <button
            type="button"
            class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white ring-1 ring-white/15 transition hover:bg-white/20 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#67FFD0]"
            aria-label="Tutup menu admin"
            data-admin-menu-close
        >
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
                <path d="M6 6l12 12M18 6 6 18" />
            </svg>
        </button>
    </div>

    <div class="px-6 py-8 text-center">
        <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full border-2 border-[#10B981] bg-[#DDFBF0] text-2xl font-black text-[#007A5A]">
            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
        </div>
        <h1 class="mt-5 text-2xl font-black text-[#67FFD0]">Admin Panel</h1>
        <p class="text-sm font-semibold text-slate-300">Waterfalls Management</p>
    </div>

    <nav class="grid gap-1 px-3 pb-5 lg:block lg:px-0">
        <a href="{{ route('admin.dashboard') }}" class="dash-nav-link {{ request()->routeIs('admin.dashboard') ? 'dash-nav-link-active' : '' }}">
            <x-icon name="home" class="h-5 w-5" /> Dashboard
        </a>
        <a href="{{ route('admin.services.index') }}" class="dash-nav-link {{ request()->routeIs('admin.services.*') ? 'dash-nav-link-active' : '' }}">
            <x-icon name="guide" class="h-5 w-5" /> Layanan
        </a>
        <a href="{{ route('admin.bookings.index') }}" class="dash-nav-link {{ request()->routeIs('admin.bookings.*') ? 'dash-nav-link-active' : '' }}">
            <x-icon name="calendar" class="h-5 w-5" /> Booking
        </a>
        <a href="{{ route('admin.payments.index') }}" class="dash-nav-link {{ request()->routeIs('admin.payments.*') ? 'dash-nav-link-active' : '' }}">
            <x-icon name="receipt" class="h-5 w-5" /> Payments
        </a>
        <a href="{{ route('admin.reports.transactions') }}" class="dash-nav-link {{ request()->routeIs('admin.reports.*') ? 'dash-nav-link-active' : '' }}">
            <x-icon name="history" class="h-5 w-5" /> Reports
        </a>
        <a href="{{ route('admin.users.index') }}" class="dash-nav-link {{ request()->routeIs('admin.users.*') ? 'dash-nav-link-active' : '' }}">
            <x-icon name="user" class="h-5 w-5" /> Users
        </a>
        <a href="{{ route('admin.galleries.index') }}" class="dash-nav-link {{ request()->routeIs('admin.galleries.*') ? 'dash-nav-link-active' : '' }}">
            <x-icon name="camera" class="h-5 w-5" /> Galeri
        </a>
        <a href="{{ route('admin.reviews.index') }}" class="dash-nav-link {{ request()->routeIs('admin.reviews.*') ? 'dash-nav-link-active' : '' }}">
            <x-icon name="star" class="h-5 w-5" /> Review
        </a>
        <a href="{{ route('admin.site-settings.edit') }}" class="dash-nav-link {{ request()->routeIs('admin.site-settings.*') ? 'dash-nav-link-active' : '' }}">
            <x-icon name="settings" class="h-5 w-5" /> Site Settings
        </a>
    </nav>

    <div class="border-t border-white/10 p-6 lg:absolute lg:bottom-0 lg:left-0 lg:right-0">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="flex items-center gap-3 text-sm font-black text-slate-200 transition hover:text-white">
                <x-icon name="logout" class="h-5 w-5" /> Logout
            </button>
        </form>
    </div>
</aside>
