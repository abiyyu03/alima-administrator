@extends('layouts.app')

@section('title', 'Manajemen User')
@section('header', 'Manajemen User')
@section('subheader', 'Daftar akun pengguna sistem')

@section('content')
<x-card title="Daftar User">

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

    <x-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Email</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Role</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Linked Tutor</th>
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
            </tr>
            @empty
            <tr>
                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">Tidak ada user ditemukan.</td>
            </tr>
            @endforelse
        </tbody>
    </x-table>

    <div class="mt-4">{{ $users->links() }}</div>
</x-card>
@endsection
