@extends('layouts.app')

@section('title', 'Tambah User')
@section('header', 'Tambah User')

@section('breadcrumb')
    <a href="{{ route('users.index') }}" class="hover:text-green-600">Manajemen User</a>
    <span class="mx-2">/</span>
    <span class="text-gray-700 font-medium">Tambah</span>
@endsection

@section('content')
<form method="POST" action="{{ route('users.store') }}">
    @csrf
    <div class="max-w-xl mx-auto">
        <x-card title="Data Akun">
            <div class="space-y-4">
                <x-input label="Nama Lengkap" name="name" :value="old('name')"
                    placeholder="Nama pengguna" :error="$errors->first('name')" />

                <x-input label="Email" name="email" type="email" :value="old('email')"
                    placeholder="email@contoh.com" :error="$errors->first('email')" />

                <x-input label="Password" name="password" type="password"
                    placeholder="Min. 8 karakter" :error="$errors->first('password')" />

                <x-input label="Konfirmasi Password" name="password_confirmation" type="password"
                    placeholder="Ulangi password" />

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Role</label>
                    <select name="role_id"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white @error('role_id') border-red-400 @enderror">
                        <option value="">Pilih role...</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>
                                {{ ucfirst($role->name) }} — {{ $role->description }}
                            </option>
                        @endforeach
                    </select>
                    @error('role_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Link ke Tutor <span class="text-gray-400 font-normal text-xs">(opsional)</span>
                    </label>
                    <select name="tutor_id"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white @error('tutor_id') border-red-400 @enderror">
                        <option value="">Tidak di-link ke tutor</option>
                        @foreach($tutors as $tutor)
                            <option value="{{ $tutor->id }}" @selected(old('tutor_id') == $tutor->id)>
                                {{ $tutor->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('tutor_id')
                        <p class="mt-1 text-xs text-red-500">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-400">Hanya tutor yang belum punya akun yang ditampilkan.</p>
                </div>
            </div>
        </x-card>

        <div class="flex justify-end gap-3 mt-4">
            <x-btn href="{{ route('users.index') }}" variant="outline">Batal</x-btn>
            <x-btn type="submit">Simpan</x-btn>
        </div>
    </div>
</form>
@endsection
