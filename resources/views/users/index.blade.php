@extends('layouts.app')

@section('title', 'Manajemen User')
@section('header', 'Manajemen User')
@section('subheader', 'Kelola akun pengguna sistem')

@section('content')
<x-card title="Daftar User">
    <x-slot:actions>
        <x-btn href="{{ route('users.create') }}">+ Tambah User</x-btn>
    </x-slot:actions>

    {{-- Filter --}}
    <form method="GET" class="flex flex-wrap gap-3 mb-4">
        <input type="text" name="search" value="{{ $search }}"
            placeholder="Cari nama atau email..."
            class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 w-full sm:w-64">
        <select name="role_id"
            class="rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
            <option value="">Semua Role</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}" @selected($roleId == $role->id)>{{ ucfirst($role->name) }}</option>
            @endforeach
        </select>
        <x-btn type="submit" variant="outline" size="sm">Filter</x-btn>
        @if($search || $roleId)
            <x-btn href="{{ route('users.index') }}" variant="outline" size="sm">Reset</x-btn>
        @endif
    </form>

    @if(session('success'))
        <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <x-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Role</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Linked Tutor</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($users as $user)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-500 text-sm">{{ $users->firstItem() + $loop->index }}</td>
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-green-100 text-green-700 flex items-center justify-center text-xs font-bold shrink-0">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </div>
                        <span class="font-medium text-gray-800 text-sm">{{ $user->name }}</span>
                        @if($user->id === auth()->id())
                            <span class="text-xs bg-green-100 text-green-600 px-1.5 py-0.5 rounded-full">Kamu</span>
                        @endif
                    </div>
                </td>
                <td class="px-4 py-3 text-gray-600 text-sm">{{ $user->email }}</td>
                <td class="px-4 py-3 hidden md:table-cell">
                    @php
                        $roleColor = match($user->role?->name) {
                            'superadmin' => 'bg-purple-100 text-purple-700',
                            'tutor'      => 'bg-blue-100 text-blue-700',
                            default      => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $roleColor }}">
                        {{ ucfirst($user->role?->name ?? '-') }}
                    </span>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600 hidden lg:table-cell">
                    {{ $user->tutor?->name ?? '-' }}
                </td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <x-btn href="{{ route('users.edit', $user) }}" size="sm" variant="outline">Edit</x-btn>
                        @if($user->id !== auth()->id())
                            <form method="POST" action="{{ route('users.destroy', $user) }}"
                                onsubmit="return confirm('Hapus user {{ $user->name }}?')">
                                @csrf @method('DELETE')
                                <x-btn type="submit" size="sm" variant="danger">Hapus</x-btn>
                            </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-4 py-8 text-center text-sm text-gray-400">Tidak ada user ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    <div class="mt-4">{{ $users->links() }}</div>
</x-card>
@endsection
