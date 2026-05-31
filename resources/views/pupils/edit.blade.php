@extends('layouts.app')

@section('title', 'Edit Siswa')
@section('header', 'Edit Siswa')

@section('breadcrumb')
    <a href="{{ route('pupils.index') }}" class="hover:text-green-600">Siswa</a>
    <span class="mx-2">/</span>
    <span class="text-gray-700 font-medium">Edit</span>
@endsection

@section('content')
<form method="POST" action="{{ route('pupils.update', $pupil) }}">
    @csrf @method('PUT')
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Kolom Kiri --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Identitas --}}
            <x-card title="Identitas Siswa">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <x-input label="Nama Lengkap" name="name" :value="old('name', $pupil->name)"
                            placeholder="Nama lengkap siswa" :error="$errors->first('name')" />
                    </div>

                    <x-input label="Tanggal Lahir" name="dob" type="date"
                        :value="old('dob', $pupil->dob?->format('Y-m-d'))"
                        :error="$errors->first('dob')" />

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jenis Kelamin</label>
                        <select name="gender"
                            class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400 bg-white">
                            <option value="">— Pilih —</option>
                            <option value="male"   {{ old('gender', $pupil->gender) === 'male'   ? 'selected' : '' }}>Laki-laki</option>
                            <option value="female" {{ old('gender', $pupil->gender) === 'female' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>

                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <label class="inline-flex items-center gap-2 cursor-pointer select-none">
                            <input type="hidden" name="active_status" value="0">
                            <input type="checkbox" name="active_status" id="active_status" value="1"
                                {{ old('active_status', $pupil->active_status) ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-400">
                            <span class="text-sm text-gray-700">Siswa Aktif</span>
                        </label>
                    </div>
                </div>
            </x-card>

            {{-- Pendaftaran Kelas --}}
            <x-card title="Pendaftaran Kelas">
                <p class="text-xs text-gray-400 mb-3">Pilih satu atau lebih kelas.</p>

                @error('class_ids')
                    <div class="mb-3 text-xs text-red-500 bg-red-50 border border-red-200 rounded-lg px-3 py-2">{{ $message }}</div>
                @enderror

                @php
                    $grouped = $classes->groupBy(fn($c) => $c->courseType->name);
                @endphp

                <div class="space-y-5">
                    @foreach($grouped as $typeName => $group)
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wide text-gray-400 mb-2">{{ $typeName }}</p>
                        <div class="space-y-2">
                            @foreach($group as $class)
                            @php
                                $isChecked = in_array($class->id, old('class_ids', $selectedIds));
                            @endphp
                            <div x-data="{ checked: {{ $isChecked ? 'true' : 'false' }} }"
                                class="rounded-lg border transition-colors"
                                :class="checked ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-white'">
                                <label class="flex items-center gap-3 px-3 py-2.5 cursor-pointer">
                                    <input type="checkbox" name="class_ids[]" value="{{ $class->id }}"
                                        x-model="checked"
                                        class="rounded border-gray-300 text-green-600 focus:ring-green-400 shrink-0">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800">{{ $class->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $class->grade->name }}</p>
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </x-card>

        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">

            <x-card>
                <div class="space-y-2.5">
                    <x-btn type="submit" class="w-full justify-center">Simpan Perubahan</x-btn>
                    <x-btn href="{{ route('pupils.index') }}" variant="outline" class="w-full justify-center">Batal</x-btn>
                </div>
            </x-card>

            <x-card title="Info Siswa">
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-400 mb-1">Kode Siswa</p>
                        <p class="text-sm font-mono font-medium text-gray-700">{{ $pupil->code }}</p>
                    </div>
                    <div class="border-t border-gray-100 pt-3">
                        <p class="text-xs text-gray-400 mb-1">Terdaftar</p>
                        <p class="text-sm text-gray-600">{{ $pupil->created_at->translatedFormat('d F Y') }}</p>
                    </div>
                    <div class="border-t border-gray-100 pt-3">
                        <label class="block text-xs text-gray-400 mb-1">
                            Rate Development Class
                            <span class="text-gray-300">(Rp/sesi, 0 = gratis)</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">Rp</span>
                            <input type="number" name="dev_class_rate"
                                value="{{ old('dev_class_rate', $pupil->dev_class_rate) }}"
                                min="0" placeholder="0"
                                class="w-full rounded-lg border border-gray-300 pl-9 pr-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                        </div>
                    </div>
                </div>
            </x-card>

            <x-card>
                <p class="text-xs text-gray-500 mb-3">Hapus data siswa ini secara permanen.</p>
                <x-btn type="button" variant="danger" class="w-full justify-center" x-data
                    @click="$store.deleteConfirm.show('Hapus siswa {{ addslashes($pupil->name) }}? Tindakan ini tidak dapat dibatalkan.', 'del-pupil-edit')">Hapus Siswa</x-btn>
            </x-card>

        </div>

    </div>
</form>

<form method="POST" action="{{ route('pupils.destroy', $pupil) }}" id="del-pupil-edit">
    @csrf @method('DELETE')
</form>
@endsection
