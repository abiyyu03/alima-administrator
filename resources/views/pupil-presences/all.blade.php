@extends('layouts.app')

@section('title', 'Presensi Siswa')
@section('header', 'Presensi Siswa')
@section('subheader', 'Kehadiran seluruh siswa')

@section('content')
<x-card title="Daftar Siswa">

    {{-- Filter --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-4">
        <input type="text" name="search" value="{{ $search }}"
            placeholder="Cari nama siswa..."
            class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 w-full sm:w-56">
        <select name="class_id"
            class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
            <option value="">Semua Kelas</option>
            @foreach($classes as $class)
                <option value="{{ $class->id }}" @selected($classId == $class->id)>
                    {{ $class->name }} ({{ $class->grade->name }})
                </option>
            @endforeach
        </select>
        <x-btn type="submit" variant="outline" size="sm">Filter</x-btn>
        @if($search || $classId)
            <x-btn href="{{ route('pupil-presences.index') }}" variant="outline" size="sm">Reset</x-btn>
        @endif
    </form>

    <x-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Kelas</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Total Sesi</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Hadir</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Tidak Hadir</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">% Hadir</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($pupils as $pupil)
            @php
                $hadir  = $pupil->total_hadir ?? 0;
                $total  = $pupil->total_sesi  ?? 0;
                $absen  = $total - $hadir;
                $persen = $total > 0 ? round($hadir / $total * 100) : 0;
                $persenColor = $persen >= 80 ? 'text-green-600' : ($persen >= 60 ? 'text-amber-500' : 'text-red-500');
            @endphp
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-400 text-sm">{{ $pupils->firstItem() + $loop->index }}</td>
                <td class="px-4 py-3">
                    <div class="font-medium text-gray-800">{{ $pupil->name }}</div>
                    <div class="text-xs font-mono text-gray-400">{{ $pupil->code }}</div>
                </td>
                <td class="px-4 py-3 hidden md:table-cell">
                    @forelse($pupil->classes as $class)
                        <div class="text-sm text-gray-700 leading-snug">{{ $class->name }}
                            <span class="text-xs text-gray-400">({{ $class->grade->name }})</span>
                        </div>
                    @empty
                        <span class="text-xs text-gray-400">—</span>
                    @endforelse
                </td>
                <td class="px-4 py-3 text-center text-sm text-gray-600">{{ $total > 0 ? $total : '-' }}</td>
                <td class="px-4 py-3 text-center">
                    <span class="font-bold text-green-600">{{ $hadir }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                    <span class="{{ $absen > 0 ? 'text-red-500' : 'text-gray-400' }}">{{ $absen }}</span>
                </td>
                <td class="px-4 py-3 text-center">
                    @if($total > 0)
                        <span class="font-semibold {{ $persenColor }}">{{ $persen }}%</span>
                    @else
                        <span class="text-gray-400 text-xs">-</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <x-btn href="{{ route('pupils.presences', $pupil) }}" size="sm" variant="outline">
                        Detail
                    </x-btn>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-10 text-center text-sm text-gray-400">
                    Tidak ada siswa ditemukan.
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    <div class="mt-4">{{ $pupils->links() }}</div>
</x-card>
@endsection
