@extends('layouts.app')

@section('title', 'Presensi — ' . $pupil->name)
@section('header', 'Detail Presensi Siswa')
@section('subheader', $pupil->name . ' · ' . $pupil->schoolClass->name)

@section('breadcrumb')
    <a href="{{ route('pupil-presences.index') }}" class="hover:text-green-600">Presensi Siswa</a>
    <span class="mx-2">/</span>
    <span class="text-gray-700 font-medium">{{ $pupil->name }}</span>
@endsection

@section('content')

{{-- Info Cards --}}
<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
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

{{-- Pupil Info --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm px-4 py-3 flex flex-wrap items-center gap-4 mb-6 text-sm text-gray-600">
    <div>
        <span class="text-gray-400 text-xs">Kode</span>
        <p class="font-mono font-medium text-gray-800">{{ $pupil->code }}</p>
    </div>
    <div>
        <span class="text-gray-400 text-xs">Kelas</span>
        <p class="font-medium text-gray-800">{{ $pupil->schoolClass->name }}</p>
    </div>
    <div>
        <span class="text-gray-400 text-xs">Grade</span>
        <p class="font-medium text-gray-800">{{ $pupil->schoolClass->grade->name }}</p>
    </div>
    <div>
        <span class="text-gray-400 text-xs">Status</span>
        <p>
            @if($pupil->active_status)
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Aktif</span>
            @else
                <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Nonaktif</span>
            @endif
        </p>
    </div>
</div>

{{-- Presence Table --}}
<x-card title="Riwayat Kehadiran">
    <x-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tanggal</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kelas</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Catatan</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($presences as $i => $presence)
            @php
                $session = $presence->classSession;
                $statusLabel = match($presence->status) {
                    'presence'   => ['Hadir',  'bg-green-100 text-green-700'],
                    'absent'     => ['Alpha',  'bg-red-100 text-red-700'],
                    'sick'       => ['Sakit',  'bg-amber-100 text-amber-700'],
                    'permission' => ['Izin',   'bg-blue-100 text-blue-700'],
                    default      => [$presence->status, 'bg-gray-100 text-gray-600'],
                };
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-400 text-sm">{{ $presences->firstItem() + $loop->index }}</td>
                <td class="px-4 py-3">
                    <div class="font-medium text-gray-800 text-sm">{{ $session->date->translatedFormat('d M Y') }}</div>
                    <div class="text-xs text-gray-400">{{ $session->date->translatedFormat('l') }}</div>
                </td>
                <td class="px-4 py-3">
                    <div class="font-medium text-gray-800 text-sm">{{ $session->schoolClass->name }}</div>
                    @if($session->schoolClass->courseType)
                    <span class="text-xs px-1.5 py-0.5 rounded-full font-medium
                        {{ $session->schoolClass->courseType->name === 'Regular' ? 'bg-blue-100 text-blue-600' : 'bg-purple-100 text-purple-600' }}">
                        {{ $session->schoolClass->courseType->name }}
                    </span>
                    @endif
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
                <td colspan="5" class="px-4 py-10 text-center text-sm text-gray-400">
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
