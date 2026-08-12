<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Jadwal Kerja') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">

    <!-- PWA Meta Tags -->
    <meta name="theme-color" content="#232B3A">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo.png') }}">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js', { scope: '/' })
                    .then(reg => console.log('Service Worker registered successfully!', reg.scope))
                    .catch(err => console.log('Service Worker registration failed:', err));
            });
        }

        // Custom PWA Install prompt handler
        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', (e) => {
            // Prevent Chrome 67 and earlier from automatically showing the prompt
            e.preventDefault();
            // Stash the event so it can be triggered later.
            deferredPrompt = e;
            // Show the install button container (it is visible by default now, but let's make sure)
            const installContainer = document.getElementById('pwa-install-container');
            if (installContainer) {
                installContainer.classList.remove('hidden');
            }
        });

        window.addEventListener('DOMContentLoaded', () => {
            const installBtn = document.getElementById('pwa-install-btn');
            if (installBtn) {
                installBtn.addEventListener('click', async () => {
                    if (!deferredPrompt) return;
                    // Show the install prompt
                    deferredPrompt.prompt();
                    // Wait for the user to respond to the prompt
                    const { outcome } = await deferredPrompt.userChoice;
                    console.log(`User response to the install prompt: ${outcome}`);
                    // We've used the prompt, and can't use it again, discard it
                    deferredPrompt = null;
                    // Hide the install button
                    const installContainer = document.getElementById('pwa-install-container');
                    if (installContainer) {
                        installContainer.classList.add('hidden');
                    }
                });
            }
        });

        window.addEventListener('appinstalled', () => {
            // Hide the install-prompts
            const installContainer = document.getElementById('pwa-install-container');
            if (installContainer) {
                installContainer.classList.add('hidden');
            }
            console.log('Jadwal Kerja App was installed successfully!');
        });
    </script>
</head>
<body class="font-sans antialiased bg-ice-blue text-dark-navy">
    <div class="min-h-screen flex flex-col md:flex-row">
        
        <!-- Sidebar (Desktop) / Topbar (Mobile) -->
        <aside class="w-full md:w-64 bg-tech-blue flex flex-col shadow-lg z-10">
            <!-- Logo -->
            <div class="p-6 border-b border-steel-gray flex items-center justify-center md:justify-start">
                <div class="w-12 h-12 rounded-full flex items-center justify-center bg-white shadow-skeuo-card overflow-hidden p-1">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-full h-full object-contain">
                </div>
                <h1 class="ml-3 text-xl font-display font-bold text-ice-blue uppercase tracking-wider" style="text-shadow: 0 2px 2px rgba(0,0,0,0.5);">Jadwal</h1>
            </div>

            <!-- Navigation Tabs -->
            <nav class="flex-1 pt-4 md:px-2 flex md:flex-col overflow-x-auto md:overflow-x-visible">
                @php
                    $route = Route::currentRouteName();
                @endphp

                <a href="{{ url('/') }}" class="nav-tab {{ $route === 'dashboard' || $route === 'welcome' || $route == '' ? 'nav-tab-active' : 'nav-tab-inactive' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Dashboard
                </a>

                <a href="{{ route('projects.create') }}" class="nav-tab {{ Str::startsWith($route, 'projects.create') ? 'nav-tab-active' : 'nav-tab-inactive' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Proyek Baru
                </a>

                <a href="{{ route('reports.index') }}" class="nav-tab {{ Str::startsWith($route, 'reports') ? 'nav-tab-active' : 'nav-tab-inactive' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Laporan
                </a>

                <a href="{{ route('groups.index') }}" class="nav-tab {{ Str::startsWith($route, 'groups') ? 'nav-tab-active' : 'nav-tab-inactive' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    Grup Proyek
                </a>

                <a href="{{ route('teams.index') }}" class="nav-tab {{ Str::startsWith($route, 'teams') ? 'nav-tab-active' : 'nav-tab-inactive' }}">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Tim
                </a>
            </nav>

            <!-- PWA Install Button (Kustom) -->
            <div id="pwa-install-container" class="hidden px-4 py-2">
                <button id="pwa-install-btn" class="w-full flex items-center justify-center py-2 px-3 text-xs bg-brass-gold hover:bg-brass-gold/90 text-dark-navy border border-brass-gold/70 shadow-skeuo-btn rounded-md font-display font-bold uppercase tracking-wider active:shadow-skeuo-btn-pressed active:translate-y-px transition-all">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    Instal Aplikasi
                </button>
            </div>

            <!-- User Profile (Bottom) -->
            <div class="p-4 border-t border-steel-gray bg-dark-navy/20">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-md border border-steel-gray bg-ice-blue overflow-hidden shadow-inner flex-shrink-0 flex items-center justify-center">
                        <span class="font-display font-bold text-dark-navy">{{ substr(Auth::user()->name ?? 'U', 0, 1) }}</span>
                    </div>
                    <div class="ml-3 flex-1 overflow-hidden">
                        <p class="text-sm font-medium text-ice-blue truncate">{{ Auth::user()->name ?? 'User Name' }}</p>
                        <p class="text-xs text-steel-gray truncate">{{ Auth::user()->role ?? 'Role' }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="ml-2 p-1 text-steel-gray hover:text-ice-blue transition-colors rounded" title="Logout">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-screen overflow-hidden">
            <div id="main-scroll-area" class="flex-1 overflow-y-auto p-4 md:p-8 animate-fade-in relative">
                @if (session('success'))
                    <div class="mb-6 skeuo-card bg-gauge-green/10 border-gauge-green p-4 flex items-center">
                        <svg class="w-6 h-6 text-gauge-green mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-gauge-green font-medium">{{ session('success') }}</p>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 skeuo-card bg-gauge-red/10 border-gauge-red p-4 flex items-center">
                        <svg class="w-6 h-6 text-gauge-red mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-gauge-red font-medium">{{ session('error') }}</p>
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Custom Confirm Modal -->
    <div id="custom-confirm-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-dark-navy/50 backdrop-blur-sm hidden opacity-0 transition-opacity duration-300">
        <div class="skeuo-card p-6 md:p-8 max-w-sm w-full mx-4 transform scale-95 transition-transform duration-300">
            <h3 class="text-xl font-display font-bold text-dark-navy mb-2 flex items-center">
                <svg class="w-6 h-6 text-gauge-red mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                Konfirmasi
            </h3>
            <p id="confirm-modal-message" class="text-steel-gray mb-6">Apakah Anda yakin?</p>
            <div class="flex justify-end space-x-3">
                <button type="button" onclick="closeConfirmModal()" class="skeuo-btn-secondary py-2 px-4 text-sm">Batal</button>
                <button type="button" id="confirm-modal-yes" class="py-2 px-4 text-sm bg-gauge-red text-white border border-[#8a2f20] shadow-[inset_0_1px_0_rgba(255,255,255,0.2),_0_2px_4px_rgba(0,0,0,0.2)] rounded-md font-display font-bold uppercase tracking-wider active:shadow-[inset_0_2px_4px_rgba(0,0,0,0.3)] active:translate-y-px transition-all">Lanjutkan</button>
            </div>
        </div>
    </div>

    <script>
        // Preserve Scroll Position
        document.addEventListener("DOMContentLoaded", function(event) { 
            const scrollArea = document.getElementById('main-scroll-area');
            const scrollPos = sessionStorage.getItem('scrollpos');
            if (scrollPos && scrollArea) {
                scrollArea.scrollTo(0, parseInt(scrollPos));
            }
        });

        window.addEventListener("beforeunload", function(e) {
            const scrollArea = document.getElementById('main-scroll-area');
            if(scrollArea) {
                sessionStorage.setItem('scrollpos', scrollArea.scrollTop);
            }
        });

        let currentConfirmForm = null;

        function showConfirmModal(message, form) {
            currentConfirmForm = form;
            document.getElementById('confirm-modal-message').innerText = message;
            
            const modal = document.getElementById('custom-confirm-modal');
            const modalCard = modal.querySelector('.skeuo-card');
            
            modal.classList.remove('hidden');
            void modal.offsetWidth;
            
            modal.classList.remove('opacity-0');
            modalCard.classList.remove('scale-95');
        }

        function closeConfirmModal() {
            const modal = document.getElementById('custom-confirm-modal');
            const modalCard = modal.querySelector('.skeuo-card');
            
            modal.classList.add('opacity-0');
            modalCard.classList.add('scale-95');
            
            setTimeout(() => {
                modal.classList.add('hidden');
                currentConfirmForm = null;
            }, 300);
        }

        document.getElementById('confirm-modal-yes').addEventListener('click', function() {
            if (currentConfirmForm) {
                currentConfirmForm.submit();
            }
        });
    </script>
</body>
</html>
