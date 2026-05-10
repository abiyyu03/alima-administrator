@extends('layouts.app')

@section('title', 'Kelas')
@section('header', 'Kelas')
@section('subheader', 'Kelola data kelas')

@section('content')
<x-card title="Daftar Kelas">
    <x-slot:actions>
        <x-btn href="{{ route('classes.create') }}">+ Tambah Kelas</x-btn>
    </x-slot:actions>

    <x-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama Kelas</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Tingkatan</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Jenis Kursus</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Mata Pelajaran</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Siswa</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($classes as $class)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-500 text-sm">{{ $classes->firstItem() + $loop->index }}</td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ $class->name }}</td>
                <td class="px-4 py-3 hidden sm:table-cell">
                    <x-badge color="green">{{ $class->grade->name }}</x-badge>
                </td>
                <td class="px-4 py-3 hidden md:table-cell">
                    <x-badge color="{{ $class->courseType->name === 'private' ? 'yellow' : 'green' }}">
                        {{ $class->courseType->name }}
                    </x-badge>
                </td>
                <td class="px-4 py-3 hidden lg:table-cell text-sm text-gray-600">
                    @if($class->subject)
                        {{ $class->subject->name }}
                    @else
                        <span class="text-gray-300">—</span>
                    @endif
                </td>
                <td class="px-4 py-3 text-center hidden sm:table-cell">
                    <x-badge color="gray">{{ $class->pupils_count }}</x-badge>
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <x-btn href="{{ route('classes.edit', $class) }}" size="sm" variant="outline">Edit</x-btn>
                        <form method="POST" action="{{ route('classes.destroy', $class) }}" id="del-class-{{ $class->id }}">
                            @csrf @method('DELETE')
                        </form>
                        <x-btn type="button" size="sm" variant="danger" x-data
                            @click="$store.deleteConfirm.show('Hapus kelas {{ addslashes($class->name) }}? Tindakan ini tidak dapat dibatalkan.', 'del-class-{{ $class->id }}')">Hapus</x-btn>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-400 text-sm">Belum ada data kelas.</td>
            </tr>
            @endforelse
        </tbody>
    </x-table>
    <div class="mt-4">{{ $classes->links() }}</div>
</x-card>
@endsection
