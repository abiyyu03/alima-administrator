@extends('layouts.app')

@section('title', 'Tambah Siswa')
@section('header', 'Tambah Siswa')

@section('breadcrumb')
    <a href="{{ route('pupils.index') }}" class="hover:text-green-600">Siswa</a>
    <span class="mx-2">/</span>
    <span class="text-gray-700 font-medium">Tambah</span>
@endsection

@section('content')
<form method="POST" action="{{ route('pupils.store') }}">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Data Siswa --}}
        <div class="lg:col-span-2">
            <x-card title="Data Siswa">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-input label="Kode Siswa" name="code" :value="old('code')"
                        placeholder="Mis. SWA-001" :error="$errors->first('code')" />

                    <div class="sm:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kelas <span class="text-red-500">*</span></label>
                        <select name="class_id"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white @error('class_id') border-red-400 @enderror">
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->id }}" {{ old('class_id') == $class->id ? 'selected' : '' }}>
                                    {{ $class->name }} — {{ $class->grade->name }} ({{ $class->courseType->name }})
                                </option>
                            @endforeach
                        </select>
                        @error('class_id')
                            <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="sm:col-span-2">
                        <x-input label="Nama Lengkap" name="name" :value="old('name')"
                            placeholder="Nama siswa" :error="$errors->first('name')" />
                    </div>

                    <x-input label="Tanggal Lahir" name="dob" type="date" :value="old('dob')"
                        :error="$errors->first('dob')" />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                        <select name="gender"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                            <option value="">-- Pilih --</option>
                            <option value="male"   {{ old('gender') === 'male'   ? 'selected' : '' }}>Laki-laki</option>
                            <option value="female" {{ old('gender') === 'female' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Rate Development Class <span class="text-gray-400 font-normal">(Rp/sesi, 0 = gratis)</span>
                        </label>
                        <input type="number" name="dev_class_rate" value="{{ old('dev_class_rate', 0) }}"
                            min="0" placeholder="0"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    </div>

                    <div class="sm:col-span-2 flex items-center gap-3 pt-1">
                        <input type="hidden" name="active_status" value="0">
                        <input type="checkbox" name="active_status" id="active_status" value="1"
                            {{ old('active_status', '1') ? 'checked' : '' }}
                            class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-400">
                        <label for="active_status" class="text-sm text-gray-700">Siswa Aktif</label>
                    </div>
                </div>
            </x-card>
        </div>

        {{-- Sidebar Actions --}}
        <div class="space-y-4">
            <x-card>
                <div class="space-y-3">
                    <x-btn type="submit" class="w-full justify-center">Simpan Siswa</x-btn>
                    <x-btn href="{{ route('pupils.index') }}" variant="outline" class="w-full justify-center">Batal</x-btn>
                </div>
            </x-card>
        </div>

    </div>
</form>
@endsection
