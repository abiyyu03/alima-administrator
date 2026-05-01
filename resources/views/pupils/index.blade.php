@extends('layouts.app')

@section('title', 'Data Siswa')
@section('header', 'Data Siswa')
@section('subheader', 'Kelola data seluruh siswa bimbingan')

@section('content')

<x-card title="Daftar Siswa">
    <x-slot:actions>
        <x-btn href="{{ route('pupils.create') }}">+ Tambah Siswa</x-btn>
    </x-slot:actions>

    {{-- Filter Bar --}}
    <form method="GET" action="{{ route('pupils.index') }}" class="p-4 border-b border-gray-100">
        <div class="flex flex-col md:flex-row md:items-center gap-2">
            <div class="relative w-full md:w-52 shrink-0">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-4.35-4.35M17 11A6 6 0 1 1 5 11a6 6 0 0 1 12 0z" />
                </svg>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau kode..."
                    class="w-full pl-9 pr-3 py-2 rounded-lg border border-gray-300 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
            </div>

            <div class="hidden md:block w-px h-5 bg-gray-200 shrink-0"></div>

            <div class="flex flex-wrap items-center gap-2 md:ml-auto">
                <select name="class_id"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                    <option value="">Semua Kelas</option>
                    @foreach($classes as $class)
                        <option value="{{ $class->id }}" {{ $classId == $class->id ? 'selected' : '' }}>
                            {{ $class->name }} ({{ $class->grade->name }})
                        </option>
                    @endforeach
                </select>

                <select name="status"
                    class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                    <option value="">Semua Status</option>
                    <option value="1" {{ $status === '1' ? 'selected' : '' }}>Aktif</option>
                    <option value="0" {{ $status === '0' ? 'selected' : '' }}>Tidak Aktif</option>
                </select>

                <button type="submit"
                    class="px-4 py-2 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-semibold transition">
                    Terapkan
                </button>
                @if($search || $classId || $status !== null && $status !== '')
                    <a href="{{ route('pupils.index') }}"
                        class="inline-flex items-center px-3 py-2 rounded-lg border border-gray-300 text-sm text-gray-500 hover:bg-gray-50 transition">
                        Reset
                    </a>
                @endif
            </div>
        </div>
    </form>

    <x-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kode</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Kelas</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">TTL</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Gender</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase">Status</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($pupils as $pupil)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-500 text-sm">{{ $pupils->firstItem() + $loop->index }}</td>
                <td class="px-4 py-3 font-mono text-sm text-gray-600">{{ $pupil->code }}</td>
                <td class="px-4 py-3">
                    <p class="font-medium text-gray-800">{{ $pupil->name }}</p>
                </td>
                <td class="px-4 py-3 hidden md:table-cell">
                    <p class="text-sm text-gray-700">{{ $pupil->schoolClass->name }}</p>
                    <p class="text-xs text-gray-400">{{ $pupil->schoolClass->grade->name }}</p>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600 hidden lg:table-cell">
                    {{ $pupil->dob ? $pupil->dob->translatedFormat('d M Y') : '-' }}
                </td>
                <td class="px-4 py-3 text-center hidden md:table-cell">
                    @if($pupil->gender === 'male')
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Laki-laki</span>
                    @elseif($pupil->gender === 'female')
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-700">Perempuan</span>
                    @else
                        <span class="text-gray-400 text-xs">-</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-center">
                    @if($pupil->active_status)
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Aktif</span>
                    @else
                        <span class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Nonaktif</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <x-btn href="{{ route('pupils.presences', $pupil) }}" size="sm" variant="outline">Presensi</x-btn>
                        <x-btn href="{{ route('pupils.edit', $pupil) }}" size="sm" variant="outline">Edit</x-btn>
                        <form method="POST" action="{{ route('pupils.destroy', $pupil) }}"
                            onsubmit="return confirm('Hapus siswa {{ $pupil->name }}?')">
                            @csrf @method('DELETE')
                            <x-btn type="submit" size="sm" variant="danger">Hapus</x-btn>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="text-sm font-medium">Belum ada data siswa.</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    @if($pupils->hasPages())
        <div class="px-4 py-3 border-t border-gray-100">
            {{ $pupils->links() }}
        </div>
    @endif
</x-card>

@endsection
