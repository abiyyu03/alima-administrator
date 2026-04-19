@extends('layouts.app')

@section('title', 'Tambah Kelas')
@section('header', 'Tambah Kelas')

@section('breadcrumb')
    <a href="{{ route('classes.index') }}" class="hover:text-green-600">Kelas</a>
    <span class="mx-2">/</span>
    <span class="text-gray-700 font-medium">Tambah</span>
@endsection

@section('content')
<form method="POST" action="{{ route('classes.store') }}" x-data="classForm()">
    @csrf
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <x-card title="Data Kelas">
            <div class="space-y-4">
                <x-input label="Nama Kelas" name="name" :value="old('name')"
                    placeholder="Contoh: Kelas Reguler Alima 1 SMP" :error="$errors->first('name')" />

                <x-select label="Jenis Kursus" name="course_type_id" :error="$errors->first('course_type_id')"
                    x-model="courseTypeId" @change="onCourseTypeChange">
                    @foreach($courseTypes as $type)
                        <option value="{{ $type->id }}" data-name="{{ strtolower($type->name) }}"
                            @selected(old('course_type_id') == $type->id)>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </x-select>

                <x-select label="Tingkatan" name="grade_id" :error="$errors->first('grade_id')">
                    @foreach($grades as $grade)
                        <option value="{{ $grade->id }}" @selected(old('grade_id') == $grade->id)>
                            {{ $grade->name }}
                        </option>
                    @endforeach
                </x-select>

                {{-- Mata pelajaran — hanya untuk private --}}
                <div x-show="isPrivate" x-transition>
                    <x-select label="Mata Pelajaran (Private)" name="subject_id" :error="$errors->first('subject_id')">
                        @foreach($subjects as $subject)
                            <option value="{{ $subject->id }}" @selected(old('subject_id') == $subject->id)>
                                {{ $subject->name }} ({{ $subject->grade->name }})
                            </option>
                        @endforeach
                    </x-select>
                    <p class="mt-1 text-xs text-gray-400">Mata pelajaran yang diajarkan pada kelas private ini.</p>
                </div>
            </div>
        </x-card>
    </div>

    <div class="flex items-center justify-end gap-3 mt-6">
        <x-btn href="{{ route('classes.index') }}" variant="outline">Batal</x-btn>
        <x-btn type="submit">Simpan Kelas</x-btn>
    </div>
</form>

@push('scripts')
<script>
function classForm() {
    const privateTypes = @json($courseTypes->where('name', 'private')->pluck('id')->values());
    return {
        courseTypeId: '{{ old('course_type_id', '') }}',
        get isPrivate() { return privateTypes.includes(parseInt(this.courseTypeId)); },
        onCourseTypeChange(e) { this.courseTypeId = e.target.value; }
    }
}
</script>
@endpush
@endsection
