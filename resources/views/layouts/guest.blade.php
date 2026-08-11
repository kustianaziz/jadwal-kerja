<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Jadwal Kerja') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-ice-blue text-dark-navy relative overflow-hidden min-h-screen flex items-center justify-center p-4">
    
    <!-- Background Orbs -->
    <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] rounded-full bg-cyan-glow/10 blur-[100px] z-0 pointer-events-none"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-[50%] h-[50%] rounded-full bg-tech-blue/10 blur-[120px] z-0 pointer-events-none"></div>

    <div class="w-full max-w-md z-10 animate-slide-up">
        <div class="text-center mb-8">
            <div class="inline-flex w-20 h-20 rounded-full items-center justify-center bg-white shadow-skeuo-card mb-4 overflow-hidden p-2 border border-steel-gray">
                <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-contain">
            </div>
            <h1 class="text-3xl font-display font-bold text-dark-navy uppercase tracking-widest">Jadwal</h1>
        </div>

        <div class="skeuo-card p-8">
            @yield('content')
        </div>
    </div>
</body>
</html>
