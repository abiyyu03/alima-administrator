<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ auth()->user()?->theme === 'dark' ? 'dark' : '' }}" id="html-root">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Alima') }} - @yield('title', 'Dashboard')</title>
    <link rel="icon" type="image/x-icon" href="/favicon/favicon.ico">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon/favicon-16x16.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon/favicon-32x32.png">
    <link rel="apple-touch-icon" href="/favicon/apple-touch-icon.png">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#14532d">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Alima">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-100 font-sans antialiased">

    {{-- Mobile overlay --}}
    <div id="sidebarOverlay"
         class="fixed inset-0 z-20 bg-black/50 hidden lg:hidden"
         aria-hidden="true">
    </div>

    <div class="flex h-screen overflow-hidden">

        {{-- Sidebar --}}
        @include('partials.sidebar')

        {{-- Main Content --}}
        <div class="flex flex-col flex-1 overflow-hidden min-w-0">

            {{-- Topbar --}}
            @include('partials.topbar')

            {{-- Page Content --}}
            <main class="flex-1 overflow-y-auto p-4 md:p-6">

                {{-- Page Header --}}
                @hasSection('header')
                    <div class="mb-5">
                        <h1 class="text-xl md:text-2xl font-bold text-gray-800">@yield('header')</h1>
                        @hasSection('subheader')
                            <p class="text-sm text-gray-500 mt-1">@yield('subheader')</p>
                        @endif
                    </div>
                @endif

                {{-- Flash Messages --}}
                @include('partials.alerts')

                {{-- Content --}}
                @yield('content')

            </main>

        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    <div x-data x-show="$store.deleteConfirm.open" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="absolute inset-0 bg-black/40" @click="$store.deleteConfirm.cancel()"></div>
        <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-gray-800 mb-1">Konfirmasi Hapus</p>
                    <p class="text-sm text-gray-500" x-text="$store.deleteConfirm.message"></p>
                </div>
            </div>
            <div class="flex gap-3 mt-5 justify-end">
                <button type="button" @click="$store.deleteConfirm.cancel()"
                    class="px-4 py-2 rounded-lg border border-gray-300 text-sm font-medium text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="button" @click="$store.deleteConfirm.confirm()"
                    class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-sm font-medium text-white transition">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </div>

    @stack('scripts')
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
    </script>
</body>
</html>
