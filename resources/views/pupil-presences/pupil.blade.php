@extends('layouts.app')

@section('title', 'Presensi — ' . $pupil->name)
@section('header', 'Detail Presensi Siswa')
@section('subheader', $pupil->name . ' · ' . $pupil->classes->pluck('name')->join(', '))

@section('breadcrumb')
    <a href="{{ route('pupil-presences.index') }}" class="hover:text-green-600">Presensi Siswa</a>
    <span class="mx-2">/</span>
    <span class="text-gray-700 font-medium">{{ $pupil->name }}</span>
@endsection

@section('content')

{{-- Filter Periode --}}
<form method="GET" class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-3 mb-5 flex flex-wrap items-end gap-3">
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Dari</label>
        <input type="date" name="from" value="{{ $from?->format('Y-m-d') }}"
            class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
    </div>
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Sampai</label>
        <input type="date" name="to" value="{{ $to?->format('Y-m-d') }}"
            class="rounded-lg border border-gray-300 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
    </div>
    <button type="submit"
        class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition">
        Tampilkan
    </button>
    @if($from || $to)
        <a href="{{ route('pupils.presences', $pupil) }}"
            class="px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-500 hover:bg-gray-50 transition">
            Reset
        </a>
    @endif
    @if($from || $to)
        <span class="text-xs text-gray-400 self-center">
            {{ $from?->translatedFormat('d M Y') ?? '...' }} – {{ $to?->translatedFormat('d M Y') ?? '...' }}
        </span>
    @endif
</form>

{{-- Summary Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-5">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
        <p class="text-xs text-gray-400 mb-1">Total Sesi</p>
        <p class="text-2xl font-bold text-gray-800">{{ $totalSesi }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
        <p class="text-xs text-gray-400 mb-1">Hadir</p>
        <p class="text-2xl font-bold text-green-600">{{ $totalHadir }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
        <p class="text-xs text-gray-400 mb-1">Tidak Hadir</p>
        <p class="text-2xl font-bold text-red-500">{{ $totalAbsen }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-4">
        <p class="text-xs text-gray-400 mb-1">% Kehadiran</p>
        <p class="text-2xl font-bold
            {{ $persen >= 80 ? 'text-green-600' : ($persen >= 60 ? 'text-amber-500' : 'text-red-500') }}">
            {{ $persen }}%
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-5">

    {{-- Kehadiran per Jenis Kelas --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Kehadiran per Jenis Kelas</p>
        @if($byCourseType->isEmpty())
            <p class="text-sm text-gray-400">Belum ada data.</p>
        @else
        <div class="space-y-2">
            @foreach($byCourseType as $typeName => $count)
            @php
                $badge = match($typeName) {
                    'Regular'          => 'bg-blue-100 text-blue-700',
                    'Private'          => 'bg-purple-100 text-purple-700',
                    'Development Class'=> 'bg-orange-100 text-orange-700',
                    'Trial Class'      => 'bg-gray-100 text-gray-600',
                    default            => 'bg-gray-100 text-gray-600',
                };
            @endphp
            <div class="flex items-center justify-between">
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $badge }}">{{ $typeName }}</span>
                <span class="font-bold text-gray-800">{{ $count }} sesi</span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Info Murid --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Info Siswa</p>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-400">Kode</span>
                <span class="font-mono font-medium text-gray-800">{{ $pupil->code }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Kelas</span>
                <span class="font-medium text-gray-800">{{ $pupil->classes->pluck('name')->join(', ') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Grade</span>
                <span class="text-gray-700">{{ $pupil->classes->pluck('grade.name')->filter()->unique()->join(', ') }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Status</span>
                @if($pupil->active_status)
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Aktif</span>
                @else
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Nonaktif</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Tagihan Development Class --}}
    <div class="bg-white rounded-xl border border-orange-200 shadow-sm p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide mb-3">Tagihan Development Class</p>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-400">Sesi hadir</span>
                <span class="font-bold text-gray-800">{{ $devHadir }} sesi</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-400">Rate per sesi</span>
                <span class="text-gray-700">Rp {{ number_format($devRate, 0, ',', '.') }}</span>
            </div>
            <div class="border-t border-gray-100 pt-2 flex justify-between items-center">
                <span class="font-semibold text-gray-700">Total Tagihan</span>
                <span class="text-lg font-bold text-orange-600">Rp {{ number_format($devTagihan, 0, ',', '.') }}</span>
            </div>
        </div>
        @if($devHadir === 0)
            <p class="mt-2 text-xs text-gray-400">Belum ada sesi Development Class yang dihadiri.</p>
        @endif
    </div>

</div>

{{-- Riwayat Kehadiran --}}
<x-card title="Riwayat Kehadiran">
    <x-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kelas</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Jenis</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Catatan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($presences as $presence)
            @php
                $session = $presence->classSession;
                $statusLabel = match($presence->status) {
                    'presence'   => ['Hadir',  'bg-green-100 text-green-700'],
                    'absent'     => ['Alpha',  'bg-red-100 text-red-700'],
                    'sick'       => ['Sakit',  'bg-amber-100 text-amber-700'],
                    'permission' => ['Izin',   'bg-blue-100 text-blue-700'],
                    default      => [$presence->status, 'bg-gray-100 text-gray-600'],
                };
                $typeName  = $session->schoolClass->courseType?->name ?? 'Lainnya';
                $typeBadge = match($typeName) {
                    'Regular'           => 'bg-blue-100 text-blue-600',
                    'Private'           => 'bg-purple-100 text-purple-600',
                    'Development Class' => 'bg-orange-100 text-orange-600',
                    'Trial Class'       => 'bg-gray-100 text-gray-500',
                    default             => 'bg-gray-100 text-gray-500',
                };
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-400 text-sm">{{ $presences->firstItem() + $loop->index }}</td>
                <td class="px-4 py-3">
                    <div class="font-medium text-gray-800 text-sm">{{ $session->date->translatedFormat('d M Y') }}</div>
                    <div class="text-xs text-gray-400">{{ $session->date->translatedFormat('l') }}</div>
                </td>
                <td class="px-4 py-3 font-medium text-gray-800 text-sm">{{ $session->schoolClass->name }}</td>
                <td class="px-4 py-3">
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $typeBadge }}">
                        {{ $typeName }}
                    </span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $statusLabel[1] }}">
                        {{ $statusLabel[0] }}
                    </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-500 hidden lg:table-cell">
                    {{ $presence->note ?? '-' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-10 text-center text-sm text-gray-400">
                    Belum ada data presensi untuk siswa ini.
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    @if($presences->hasPages())
    <div class="px-4 py-3 border-t border-gray-100">
        {{ $presences->links() }}
    </div>
    @endif
</x-card>

@endsection
