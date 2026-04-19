@extends('layouts.app')

@section('title', 'Data Tutor')
@section('header', 'Data Tutor')
@section('subheader', 'Kelola data tutor beserta kelas yang diampu')

@section('content')
<x-card title="Daftar Tutor">
    <x-slot:actions>
        <x-btn href="{{ route('tutors.create') }}">+ Tambah Tutor</x-btn>
    </x-slot:actions>

    <x-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">No. Telp</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Email</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Domisili</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Kelas Diampu</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($tutors as $tutor)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-500 text-sm">{{ $tutors->firstItem() + $loop->index }}</td>
                <td class="px-4 py-3">
                    <div class="font-medium text-gray-800">{{ $tutor->name }}</div>
                    @if($tutor->dob)
                        <div class="text-xs text-gray-400">{{ $tutor->dob->format('d M Y') }}</div>
                    @endif
                </td>
                <td class="px-4 py-3 text-gray-600 text-sm hidden md:table-cell">{{ $tutor->telp ?? '-' }}</td>
                <td class="px-4 py-3 text-sm hidden md:table-cell">
                    {{ $tutor->user?->email ?? '-' }}
                </td>
                <td class="px-4 py-3 text-gray-600 text-sm hidden lg:table-cell">{{ $tutor->domicille ?? '-' }}</td>
                <td class="px-4 py-3">
                    <div class="flex flex-wrap gap-1">
                        @forelse($tutor->classes as $class)
                            <x-badge color="{{ $class->courseType->name === 'private' ? 'yellow' : 'blue' }}">
                                {{ $class->name }}
                            </x-badge>
                        @empty
                            <span class="text-xs text-gray-400">-</span>
                        @endforelse
                    </div>
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <x-btn href="{{ route('tutors.edit', $tutor) }}" size="sm" variant="outline">Edit</x-btn>
                        <form method="POST" action="{{ route('tutors.destroy', $tutor) }}"
                            onsubmit="return confirm('Hapus tutor {{ $tutor->name }}?')">
                            @csrf @method('DELETE')
                            <x-btn type="submit" size="sm" variant="danger">Hapus</x-btn>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="px-4 py-8 text-center text-gray-400 text-sm">Belum ada data tutor.</td>
            </tr>
            @endforelse
        </tbody>
    </x-table>
    <div class="mt-4">{{ $tutors->links() }}</div>
</x-card>
@endsection
