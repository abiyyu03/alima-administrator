@extends('layouts.app')

@section('title', 'Gaji Tutor')
@section('header', 'Gaji Tutor')
@section('subheader', 'Rate gaji per sesi berdasarkan kelas yang diampu')

@section('content')

{{-- Summary bar --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-xs text-gray-500">Total Tutor</p>
            <p class="text-xl font-bold text-gray-800">{{ $tutors->count() }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 8h6m-5 0a3 3 0 110 6H9l3 3m-3-6h6m6 1a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <div>
            <p class="text-xs text-gray-500">Total Rate / Sesi</p>
            <p class="text-xl font-bold text-gray-800">
                Rp{{ number_format($tutors->sum('total_rate'), 0, ',', '.') }}
            </p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-yellow-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/>
            </svg>
        </div>
        <div>
            <p class="text-xs text-gray-500">Total Kelas Aktif</p>
            <p class="text-xl font-bold text-gray-800">{{ $tutors->sum('classes_count') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
            </svg>
        </div>
        <div>
            <p class="text-xs text-gray-500">Rata-rata / Tutor</p>
            <p class="text-xl font-bold text-gray-800">
                Rp{{ $tutors->count() ? number_format($tutors->avg('total_rate'), 0, ',', '.') : 0 }}
            </p>
        </div>
    </div>
</div>

{{-- Tutor cards --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
    @forelse($tutors as $tutor)
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden flex flex-col"
         x-data="{ open: false }">

        {{-- Header --}}
        <div class="p-5 flex items-center justify-between gap-3 cursor-pointer select-none"
             @click="open = !open">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-10 h-10 rounded-full bg-green-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                    {{ strtoupper(substr($tutor->name, 0, 2)) }}
                </div>
                <div class="min-w-0">
                    <p class="font-semibold text-gray-800 truncate">{{ $tutor->name }}</p>
                    <p class="text-xs text-gray-400">{{ $tutor->classes_count }} kelas</p>
                </div>
            </div>
            <div class="text-right flex-shrink-0">
                <p class="text-sm font-bold text-green-600">
                    Rp{{ number_format($tutor->total_rate, 0, ',', '.') }}
                </p>
                <p class="text-xs text-gray-400">/ sesi</p>
            </div>
        </div>

        {{-- Kelas detail (expandable) --}}
        <div x-show="open" x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="border-t border-gray-100">

            @if($tutor->classes->isEmpty())
                <p class="px-5 py-4 text-sm text-gray-400 text-center">Belum ada kelas yang diampu.</p>
            @else
                <div class="divide-y divide-gray-50">
                    @foreach($tutor->classes as $class)
                    <div class="px-5 py-3 flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-700 truncate">{{ $class->name }}</p>
                            <div class="flex items-center gap-1.5 mt-0.5">
                                <x-badge color="{{ $class->courseType->name === 'Private' ? 'yellow' : 'blue' }}" >
                                    {{ $class->courseType->name }}
                                </x-badge>
                                <x-badge color="green">{{ $class->grade->name }}</x-badge>
                            </div>
                        </div>
                        @php
                            $pivotAmount  = (int) $class->pivot->amount;
                            $isDefault    = $pivotAmount === 0;
                            $defaultRate  = strtolower($class->courseType?->name ?? '') === 'private'
                                ? (int) config('presence.tutor_rate_private')
                                : (int) config('presence.tutor_rate_regular');
                            $displayRate  = $isDefault ? $defaultRate : $pivotAmount;
                        @endphp
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-semibold {{ $isDefault ? 'text-gray-400' : 'text-gray-800' }}">
                                Rp{{ number_format($displayRate, 0, ',', '.') }}
                            </p>
                            <p class="text-xs {{ $isDefault ? 'text-gray-300' : 'text-gray-400' }}">
                                {{ $isDefault ? 'default' : '/ sesi' }}
                            </p>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Total row --}}
                <div class="px-5 py-3 bg-gray-50 flex items-center justify-between border-t border-gray-100">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Total per sesi</p>
                    <p class="text-sm font-bold text-green-600">
                        Rp{{ number_format($tutor->total_rate, 0, ',', '.') }}
                    </p>
                </div>
            @endif

            {{-- Edit link --}}
            <div class="px-5 py-3 bg-gray-50 border-t border-gray-100">
                <a href="{{ route('tutors.edit', $tutor) }}"
                   class="text-xs text-green-600 hover:text-green-800 font-medium flex items-center gap-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Ubah kelas & gaji
                </a>
            </div>
        </div>

        {{-- Expand hint --}}
        <div class="px-5 pb-3 pt-0 flex items-center justify-center" x-show="!open">
            <button @click="open = true" class="text-xs text-gray-400 hover:text-green-500 flex items-center gap-1 transition-colors">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
                Lihat detail kelas
            </button>
        </div>

    </div>
    @empty
    <div class="col-span-full">
        <div class="bg-white rounded-xl border border-gray-200 p-12 text-center">
            <p class="text-gray-400">Belum ada data tutor.</p>
            <x-btn href="{{ route('tutors.create') }}" class="mt-4">Tambah Tutor</x-btn>
        </div>
    </div>
    @endforelse
</div>

@endsection
