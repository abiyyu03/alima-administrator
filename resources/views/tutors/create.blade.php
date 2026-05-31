@extends('layouts.app')

@section('title', 'Tambah Tutor')
@section('header', 'Tambah Tutor')

@section('breadcrumb')
    <a href="{{ route('tutors.index') }}" class="hover:text-green-600">Tutor</a>
    <span class="mx-2">/</span>
    <span class="text-gray-700 font-medium">Tambah</span>
@endsection

@section('content')
    <form method="POST" action="{{ route('tutors.store') }}">
        @csrf
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            {{-- Data Diri --}}
            <div class="lg:col-span-2">
                <x-card title="Data Diri Tutor">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <x-input label="Nama Lengkap" name="name" :value="old('name')" placeholder="Nama tutor"
                                :error="$errors->first('name')" />
                        </div>
                        <x-input label="No. Telepon" name="telp" :value="old('telp')" placeholder="08xxxxxxxxxx"
                            :error="$errors->first('telp')" />
                        <x-input label="Tanggal Lahir" name="dob" type="date" :value="old('dob')" :error="$errors->first('dob')" />
                        <div class="sm:col-span-2">
                            <x-input label="Domisili" name="domicille" :value="old('domicille')" placeholder="Kota domisili"
                                :error="$errors->first('domicille')" />
                        </div>
                    </div>
                </x-card>

                <x-card title="Akun Login Tutor" class="mt-6">
                    <p class="text-xs text-gray-500 mb-4">Akun ini digunakan tutor untuk login dan mengisi presensi.</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="sm:col-span-2">
                            <x-input label="Email" name="email" type="email" :value="old('email')"
                                placeholder="email@contoh.com" :error="$errors->first('email')" />
                        </div>
                        <x-input label="Password" name="password" type="password" placeholder="Min. 8 karakter"
                            :error="$errors->first('password')" />
                        <x-input label="Konfirmasi Password" name="password_confirmation" type="password"
                            placeholder="Ulangi password" />
                    </div>
                </x-card>
            </div>

            {{-- Assign Kelas + Gaji --}}
            <div x-data="{ search: '' }">
                <x-card title="Kelas & Gaji per Sesi">
                    <p class="text-xs text-gray-500 mb-3">Centang kelas yang diampu, lalu isi nominal gaji per sesi. Kelas
                        privat menampilkan nama anaknya.</p>

                    {{-- Search --}}
                    <div class="relative mb-4">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                        </svg>
                        <input type="text" x-model="search" placeholder="Cari nama kelas, tingkatan, atau anak..."
                            class="w-full rounded-lg border border-gray-300 pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                        <button type="button" x-show="search" @click="search = ''"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    @php $groupedClasses = $classes->groupBy(fn($c) => $c->courseType->name) @endphp

                    <div class="space-y-5 max-h-[380px] overflow-y-auto pr-1">
                        @forelse($groupedClasses as $typeName => $group)
                            <div x-show="@js($group->pluck('search_key')->values()).some(s => s.includes(search.toLowerCase()))" x-cloak>
                                <p class="text-xs font-bold text-green-600 uppercase tracking-wide mb-2">{{ $typeName }}
                                </p>
                                <div class="space-y-3">
                                    @foreach ($group as $class)
                                        @php
                                            $defaultRate =
                                                strtolower($typeName) === 'private'
                                                    ? config('presence.tutor_rate_private')
                                                    : config('presence.tutor_rate_regular');
                                            // Default semua anak terpilih saat kelas private dicentang
                                            $pupilChecked = fn($pupilId) => old('pupil_ids') !== null
                                                ? in_array($pupilId, old('pupil_ids', []))
                                                : true;
                                        @endphp
                                        <div x-data="{ checked: {{ in_array($class->id, old('class_ids', [])) ? 'true' : 'false' }} }"
                                            x-show="@js($class->search_key).includes(search.toLowerCase())" x-cloak
                                            class="rounded-lg border border-gray-200 p-3 transition-colors"
                                            :class="checked ? 'bg-green-50 border-green-300' : 'bg-white'">
                                            <label class="flex items-start gap-2.5 cursor-pointer">
                                                <input type="checkbox" name="class_ids[]" value="{{ $class->id }}"
                                                    x-model="checked"
                                                    class="mt-0.5 rounded border-gray-300 text-green-600 focus:ring-green-400">
                                                <div class="flex-1 min-w-0">
                                                    <p class="text-sm font-medium text-gray-800 leading-tight">
                                                        {{ $class->name }}</p>
                                                    <p class="text-xs text-gray-400">{{ $class->grade->name }}</p>
                                                    @if (strtolower($typeName) === 'private')
                                                        @if ($class->pupils->isNotEmpty())
                                                            <p
                                                                class="text-xs text-green-600 font-medium mt-0.5 flex items-start gap-1">
                                                                <svg class="w-3 h-3 mt-0.5 flex-shrink-0" fill="none"
                                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                                                </svg>
                                                                <span>{{ $class->pupils->pluck('name')->join(', ') }}</span>
                                                            </p>
                                                        @else
                                                            <p class="text-xs text-amber-500 mt-0.5">Belum ada siswa</p>
                                                        @endif
                                                    @endif
                                                </div>
                                            </label>
                                            <div x-show="checked" x-transition class="mt-2.5 pl-6 space-y-2">
                                                @if (strtolower($typeName) === 'private' && $class->pupils->isNotEmpty())
                                                    <div>
                                                        <p class="text-xs text-gray-400 mb-1.5">Anak yang dipegang &amp; extra rate / sesi</p>
                                                        <div class="space-y-2">
                                                            @foreach ($class->pupils as $p)
                                                                <div x-data="{ on: {{ $pupilChecked($p->id) ? 'true' : 'false' }} }"
                                                                    class="flex items-center gap-2">
                                                                    <label class="flex items-center gap-2 cursor-pointer flex-1 min-w-0">
                                                                        <input type="checkbox" name="pupil_ids[]"
                                                                            value="{{ $p->id }}" x-model="on"
                                                                            @checked($pupilChecked($p->id))
                                                                            class="rounded border-gray-300 text-green-600 focus:ring-green-400">
                                                                        <span class="text-xs text-gray-700 truncate">{{ $p->name }}</span>
                                                                    </label>
                                                                    <div class="relative w-24 shrink-0" x-show="on" x-transition x-cloak>
                                                                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] text-gray-400 font-medium">+Rp</span>
                                                                        <input type="number" name="pupil_extra[{{ $p->id }}]"
                                                                            value="{{ old('pupil_extra.' . $p->id, 0) }}" min="0" step="500" placeholder="0"
                                                                            class="w-full rounded-lg border border-gray-300 pl-8 pr-2 py-1 text-xs focus:outline-none focus:ring-2 focus:ring-green-400">
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endif
                                                <div>
                                                    <p class="text-xs text-gray-400 mb-1">Gaji per sesi</p>
                                                    <div class="relative">
                                                        <span
                                                            class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium">Rp</span>
                                                        <input type="number" name="amounts[{{ $class->id }}]"
                                                            value="{{ old('amounts.' . $class->id, $defaultRate) }}"
                                                            min="0" step="500" placeholder="0"
                                                            class="w-full rounded-lg border border-gray-300 pl-9 pr-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                                                    </div>
                                                </div>
                                                @if (strtolower($typeName) !== 'private')
                                                    <div>
                                                        <p class="text-xs text-gray-400 mb-1">Biaya tambahan (jarak, dll)</p>
                                                        <div class="relative">
                                                            <span
                                                                class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400 font-medium">Rp</span>
                                                            <input type="number" name="extra_fees[{{ $class->id }}]"
                                                                value="{{ old('extra_fees.' . $class->id, 0) }}"
                                                                min="0" step="500" placeholder="0"
                                                                class="w-full rounded-lg border border-gray-300 pl-9 pr-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                                                        </div>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-gray-400 text-center py-4">Belum ada kelas. Tambahkan kelas terlebih
                                dahulu.</p>
                        @endforelse

                        {{-- Empty state saat search tidak ada hasil --}}
                        <p class="text-sm text-gray-400 text-center py-4 hidden"
                            x-show="search && !@js($classes->pluck('search_key')).some(s => s.includes(search.toLowerCase()))">
                            Kelas "<span x-text="search" class="font-medium"></span>" tidak ditemukan.
                        </p>
                    </div>
                </x-card>
            </div>

        </div>

        <div class="flex items-center justify-end gap-3 mt-6">
            <x-btn href="{{ route('tutors.index') }}" variant="outline">Batal</x-btn>
            <x-btn type="submit">Simpan Tutor</x-btn>
        </div>
    </form>
@endsection
