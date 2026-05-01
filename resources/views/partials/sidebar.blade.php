{{--
    Mobile  : fixed drawer, slides in from left (translate-x), hidden by default
    Desktop : static sidebar, always visible
--}}
@php $isTutor = auth()->user()?->isTutor(); @endphp

<aside id="sidebar"
       class="fixed inset-y-0 left-0 z-30 w-64 bg-green-900 text-white flex flex-col flex-shrink-0
              -translate-x-full transition-transform duration-300 ease-in-out
              lg:static lg:translate-x-0 lg:z-auto">

    {{-- Logo --}}
    <div class="flex items-center justify-center h-16 border-b border-green-700 px-4">
        <span class="text-xl font-bold tracking-wide">Alima</span>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">

        <x-sidebar-link href="{{ route('dashboard') }}" icon="home" :active="request()->routeIs('dashboard')">
            Dashboard
        </x-sidebar-link>

        @if($isTutor)
            {{-- Tutor: hanya lihat presensi sendiri --}}
            <x-sidebar-group label="Presensi">
                <x-sidebar-link href="{{ route('my-presences') }}" icon="clipboard-document-check" :active="request()->routeIs('my-presences*')">
                    Presensi Saya
                </x-sidebar-link>
            </x-sidebar-group>
        @else
            {{-- Admin / Superadmin --}}

            {{-- Master Data --}}
            <x-sidebar-group label="Master Data">
                <x-sidebar-link href="{{ route('grades.index') }}" icon="academic-cap" :active="request()->routeIs('grades.*')">
                    Tingkatan (Grade)
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('course-types.index') }}" icon="tag" :active="request()->routeIs('course-types.*')">
                    Jenis Kursus
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('subjects.index') }}" icon="book-open" :active="request()->routeIs('subjects.*')">
                    Mata Pelajaran
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('classes.index') }}" icon="building-library" :active="request()->routeIs('classes.*')">
                    Kelas
                </x-sidebar-link>
            </x-sidebar-group>

            {{-- Tutor --}}
            <x-sidebar-group label="Tutor">
                <x-sidebar-link href="{{ route('tutors.index') }}" icon="user-group" :active="request()->routeIs('tutors.*')">
                    Data Tutor
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('tutor-salaries.index') }}" icon="banknotes" :active="request()->routeIs('tutor-salaries.*')">
                    Gaji Tutor
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('tutor-presences.index') }}" icon="clipboard-document-check" :active="request()->routeIs('tutor-presences.*')">
                    Presensi Tutor
                </x-sidebar-link>
            </x-sidebar-group>

            {{-- Siswa --}}
            <x-sidebar-group label="Siswa">
                <x-sidebar-link href="{{ route('pupils.index') }}" icon="users" :active="request()->routeIs('pupils.*')">
                    Data Siswa
                </x-sidebar-link>
                <x-sidebar-link href="{{ route('pupil-presences.index') }}" icon="clipboard-document-check" :active="request()->routeIs('pupil-presences.*')">
                    Presensi Siswa
                </x-sidebar-link>
            </x-sidebar-group>

            {{-- Pengaturan (superadmin only) --}}
            @if(auth()->user()?->role?->name === 'superadmin')
            <x-sidebar-group label="Pengaturan">
                <x-sidebar-link href="{{ route('users.index') }}" icon="cog-6-tooth" :active="request()->routeIs('users.*')">
                    Manajemen User
                </x-sidebar-link>
            </x-sidebar-group>
            @endif

        @endif

    </nav>

    {{-- User Info --}}
    <div class="border-t border-green-700 p-4">
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-green-500 flex items-center justify-center text-sm font-bold">
                {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium truncate">{{ auth()->user()?->name }}</p>
                <p class="text-xs text-green-300 truncate">{{ auth()->user()?->role?->name ?? '-' }}</p>
            </div>
        </div>
    </div>

</aside>
