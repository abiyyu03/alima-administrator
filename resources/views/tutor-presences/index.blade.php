@extends('layouts.app')

@section('title', 'Presensi Tutor')
@section('header', 'Presensi Tutor')
@section('subheader', 'Rekap kehadiran semua tutor per minggu')

@section('content')

    {{-- Week Navigator --}}
    @if (!$dateFrom && !$dateTo)
        <div class="flex items-center justify-between mb-6">
            <a href="{{ route('tutor-presences.index', array_filter(['date' => $weekStart->copy()->subWeek()->format('Y-m-d'), 'tutor_id' => $tutorId])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-600 hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                <span class="hidden sm:inline">Minggu Lalu</span>
            </a>
            <div class="text-center">
                <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold">Minggu</p>
                <p class="text-sm sm:text-base font-bold text-gray-800">
                    {{ $weekStart->translatedFormat('d M') }} – {{ $weekEnd->translatedFormat('d M Y') }}
                </p>
            </div>
            <a href="{{ route('tutor-presences.index', array_filter(['date' => $weekStart->copy()->addWeek()->format('Y-m-d'), 'tutor_id' => $tutorId])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-300 bg-white text-sm text-gray-600 hover:bg-gray-50 transition">
                <span class="hidden sm:inline">Minggu Depan</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    @else
        <div class="flex items-center gap-2 mb-6">
            <svg class="w-4 h-4 text-green-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-sm font-semibold text-gray-700">
                {{ $weekStart->translatedFormat('d M Y') }} — {{ $weekEnd->translatedFormat('d M Y') }}
            </p>
            <span class="text-xs text-gray-400">(custom range)</span>
        </div>
    @endif

    {{-- Summary Cards --}}
    @php
        $allPresences = $sessions->flatMap->tutorPresences;
        $totalSessions = $sessions->count();
        $totalHadir = $allPresences->where('status', 'presence')->count();
        $totalEarned = $allPresences->sum(fn($p) => $p->earned);
    @endphp
    <div class="flex gap-3 mb-6">
        <div class="flex-1 bg-white rounded-xl border border-gray-200 p-3 sm:p-4 shadow-sm text-center">
            <p class="text-xl sm:text-2xl font-bold text-gray-800">{{ $totalSessions }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Total Sesi</p>
        </div>
        <div class="flex-1 bg-white rounded-xl border border-gray-200 p-3 sm:p-4 shadow-sm text-center">
            <p class="text-xl sm:text-2xl font-bold text-green-600">{{ $totalHadir }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Hadir</p>
        </div>
        <div class="flex-1 bg-white rounded-xl border border-gray-200 p-3 sm:p-4 shadow-sm text-center">
            <p class="text-sm sm:text-xl font-bold text-emerald-700">Rp {{ number_format($totalEarned, 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Pendapatan</p>
        </div>
    </div>

    {{-- Filter & Table --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm" x-data="{ search: '' }">

        {{-- Filter Bar --}}
        <form method="GET" action="{{ route('tutor-presences.index') }}"
            class="p-4 border-b border-gray-100">
            <div class="flex flex-col md:flex-row md:items-center gap-2">

                {{-- Search --}}
                <div class="relative w-full md:w-52 shrink-0">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                    </svg>
                    <input type="text" x-model="search" placeholder="Cari tutor atau kelas..."
                        class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                </div>

                <div class="hidden md:block w-px h-5 bg-gray-200 shrink-0"></div>

                {{-- Filters --}}
                <div class="flex flex-wrap items-center gap-2 md:ml-auto">
                    <select name="tutor_id"
                        class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                        <option value="">Semua Tutor</option>
                        @foreach ($tutors as $t)
                            <option value="{{ $t->id }}" {{ $tutorId == $t->id ? 'selected' : '' }}>
                                {{ $t->name }}</option>
                        @endforeach
                    </select>

                    <div class="flex items-center gap-1.5">
                        <input type="date" name="date_from" value="{{ $dateFrom ?? $weekStart->format('Y-m-d') }}"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                        <span class="text-gray-400 text-sm">—</span>
                        <input type="date" name="date_to" value="{{ $dateTo ?? $weekEnd->format('Y-m-d') }}"
                            class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                    </div>

                    <button type="submit"
                        class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition">
                        Terapkan
                    </button>
                    @if ($tutorId || $dateFrom || $dateTo)
                        <a href="{{ route('tutor-presences.index') }}"
                            class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-500 hover:bg-gray-50 transition">
                            Reset
                        </a>
                    @endif
                </div>
            </div>
        </form>

        {{-- Table --}}
        <div class="p-4">
            @if ($sessions->isEmpty())
                <div class="py-12 text-center text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-sm font-medium">Belum ada sesi pada rentang tanggal ini.</p>
                </div>
            @else
                <div class="overflow-x-auto rounded-lg border border-gray-200">
                    <table class="w-full text-sm min-w-[600px]">
                        <thead>
                            <tr class="bg-gray-50 text-left border-b border-gray-200">
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tanggal
                                </th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Kelas</th>
                                <th class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Tutor</th>
                                <th
                                    class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden lg:table-cell">
                                    Materi</th>
                                <th
                                    class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-center">
                                    Status</th>
                                <th
                                    class="px-4 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide text-right">
                                    Pendapatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($sessions as $session)
                                @if ($session->tutorPresences->isEmpty())
                                    <tr class="hover:bg-gray-50"
                                        x-show="!search || '{{ strtolower($session->schoolClass->name) }}'.includes(search.toLowerCase())">
                                        <td class="px-4 py-3 text-gray-600 whitespace-nowrap">
                                            {{ \Carbon\Carbon::parse($session->date)->translatedFormat('d M Y') }}
                                            <span
                                                class="block text-xs text-gray-400">{{ \Carbon\Carbon::parse($session->date)->translatedFormat('l') }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <p class="font-medium text-gray-800">{{ $session->schoolClass->name }}</p>
                                            <p class="text-xs text-gray-400">{{ $session->schoolClass->grade->name }}</p>
                                        </td>
                                        <td colspan="4" class="px-4 py-3 text-gray-400 italic text-xs">Belum ada
                                            presensi</td>
                                    </tr>
                                @else
                                    @foreach ($session->tutorPresences as $i => $p)
                                        @php
                                            $statusMap = [
                                                'presence' => ['Hadir', 'bg-green-100 text-green-700'],
                                                'absent' => ['Alpha', 'bg-red-100 text-red-700'],
                                                'sick' => ['Sakit', 'bg-amber-100 text-amber-700'],
                                                'permission' => ['Izin', 'bg-blue-100 text-blue-700'],
                                            ];
                                            [$label, $cls] = $statusMap[$p->status] ?? [
                                                '?',
                                                'bg-gray-100 text-gray-500',
                                            ];
                                        @endphp
                                        <tr class="hover:bg-gray-50"
                                            x-show="!search || '{{ strtolower($p->tutor->name . ' ' . $session->schoolClass->name) }}'.includes(search.toLowerCase())">
                                            @if ($i === 0)
                                                <td class="px-4 py-3 text-gray-600 whitespace-nowrap align-top"
                                                    rowspan="{{ $session->tutorPresences->count() }}">
                                                    {{ \Carbon\Carbon::parse($session->date)->translatedFormat('d M Y') }}
                                                    <span
                                                        class="block text-xs text-gray-400">{{ \Carbon\Carbon::parse($session->date)->translatedFormat('l') }}</span>
                                                </td>
                                                <td class="px-4 py-3 align-top"
                                                    rowspan="{{ $session->tutorPresences->count() }}">
                                                    <p class="font-medium text-gray-800">{{ $session->schoolClass->name }}
                                                    </p>
                                                    <p class="text-xs text-gray-400">
                                                        {{ $session->schoolClass->grade->name }}</p>
                                                </td>
                                            @endif
                                            <td class="px-4 py-3">
                                                <div class="flex items-center gap-2">
                                                    <div
                                                        class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center text-xs font-bold text-green-700 shrink-0">
                                                        {{ strtoupper(substr($p->tutor->name, 0, 1)) }}
                                                    </div>
                                                    <span class="font-medium text-gray-700">{{ $p->tutor->name }}</span>
                                                </div>
                                            </td>
                                            @if ($i === 0)
                                                <td class="px-4 py-3 text-gray-500 text-xs align-top hidden lg:table-cell"
                                                    rowspan="{{ $session->tutorPresences->count() }}">
                                                    {{ $session->material ?? '—' }}
                                                </td>
                                            @endif
                                            <td class="px-4 py-3 text-center">
                                                <span
                                                    class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium {{ $cls }}">{{ $label }}</span>
                                            </td>
                                            <td
                                                class="px-4 py-3 text-right font-semibold whitespace-nowrap {{ $p->earned > 0 ? 'text-green-700' : 'text-gray-400' }}">
                                                {{ $p->earned > 0 ? 'Rp ' . number_format($p->earned, 0, ',', '.') : '—' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            @endforeach
                        </tbody>
                        @if ($totalEarned > 0)
                            <tfoot>
                                <tr class="bg-green-50 border-t-2 border-green-200">
                                    <td colspan="5" class="px-4 py-3 text-sm font-semibold text-green-800 text-right">
                                        Total Pendapatan</td>
                                    <td class="px-4 py-3 text-right font-bold text-green-700 whitespace-nowrap">
                                        Rp {{ number_format($totalEarned, 0, ',', '.') }}
                                    </td>
                                </tr>
                            </tfoot>
                        @endif
                    </table>
                </div>
            @endif
        </div>
    </div>

@endsection
