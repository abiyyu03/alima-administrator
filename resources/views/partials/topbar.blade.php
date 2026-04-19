<header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 md:px-6 flex-shrink-0">

    {{-- Left: Toggle + Breadcrumb --}}
    <div class="flex items-center gap-3 min-w-0">
        {{-- Hamburger (selalu tampil, fungsional di mobile, bisa collapse di desktop) --}}
        <button id="sidebarToggle"
                class="flex-shrink-0 p-1.5 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none transition-colors"
                aria-label="Toggle sidebar">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        @hasSection('breadcrumb')
            <nav class="hidden sm:flex items-center text-sm text-gray-500 min-w-0 truncate">
                @yield('breadcrumb')
            </nav>
        @endif
    </div>

    {{-- Right: User info + Logout --}}
    <div class="flex items-center gap-2 md:gap-3 flex-shrink-0">

        {{-- User name (hidden on very small screens) --}}
        <span class="hidden md:block text-sm text-gray-600 font-medium">
            {{ auth()->user()?->name }}
        </span>

        {{-- Logout --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm text-gray-500 hover:text-red-600 hover:bg-red-50 transition-colors">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"/>
                </svg>
                <span class="hidden sm:inline">Logout</span>
            </button>
        </form>

    </div>

</header>
