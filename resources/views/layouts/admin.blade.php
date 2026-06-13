<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin') - Sendang Gile</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F7F8FC] text-[#111827] antialiased">
    <div class="dashboard-shell">
        <header class="sticky top-0 z-40 border-b border-[#E5EAF2] bg-white/92 px-4 py-3 shadow-[0_10px_28px_rgba(15,27,45,0.06)] backdrop-blur lg:hidden">
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-xs font-black uppercase tracking-[0.16em] text-[#007A5A]">Admin</p>
                    <h1 class="truncate text-base font-black text-[#0F1B2D]">Sendang Gile Panel</h1>
                </div>
                <button
                    type="button"
                    class="inline-flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#0F1B2D] text-white shadow-[0_12px_24px_rgba(15,27,45,0.16)] transition hover:bg-[#182943] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#10B981]"
                    aria-label="Buka menu admin"
                    aria-controls="admin-sidebar"
                    aria-expanded="false"
                    data-admin-menu-toggle
                >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" aria-hidden="true">
                        <path d="M5 7h14M5 12h14M5 17h14" />
                    </svg>
                </button>
            </div>
        </header>

        <div class="fixed inset-0 z-40 hidden bg-[#0F1B2D]/45 backdrop-blur-sm lg:hidden" data-admin-sidebar-backdrop></div>

        @include('partials.admin-sidebar')

        <main class="dashboard-main">
            @include('partials.alerts')
            @yield('content')
        </main>
    </div>
</body>
</html>
