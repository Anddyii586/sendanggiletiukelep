<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Traveler Dashboard') - Sendang Gile</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#F7F8FC] text-[#111827] antialiased">
    <div class="dashboard-shell">
        @include('partials.user-sidebar')

        <main class="dashboard-main">
            @include('partials.alerts')
            @yield('content')
        </main>
    </div>
</body>
</html>
