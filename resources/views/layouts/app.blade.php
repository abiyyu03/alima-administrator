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

    @stack('scripts')
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
    </script>
</body>
</html>
