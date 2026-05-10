@extends('layouts.app')

@section('title', 'Mata Pelajaran')
@section('header', 'Mata Pelajaran')
@section('subheader', 'Kelola data mata pelajaran per tingkatan')

@section('content')
<x-card title="Daftar Mata Pelajaran">
    <x-slot:actions>
        <x-btn x-data @click="$dispatch('open-add-subject')">+ Tambah</x-btn>
    </x-slot:actions>

    <x-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Tingkatan</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($subjects as $subject)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-500 text-sm">{{ $subjects->firstItem() + $loop->index }}</td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ $subject->name }}</td>
                <td class="px-4 py-3"><x-badge color="green">{{ $subject->grade->name }}</x-badge></td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <x-btn size="sm" variant="outline" x-data @click="$dispatch('open-edit-subject', {{ $subject->toJson() }})">Edit</x-btn>
                        <form method="POST" action="{{ route('subjects.destroy', $subject) }}" id="del-subject-{{ $subject->id }}">
                            @csrf @method('DELETE')
                        </form>
                        <x-btn type="button" size="sm" variant="danger" x-data
                            @click="$store.deleteConfirm.show('Hapus mata pelajaran {{ addslashes($subject->name) }}? Tindakan ini tidak dapat dibatalkan.', 'del-subject-{{ $subject->id }}')">Hapus</x-btn>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400 text-sm">Belum ada data mata pelajaran.</td></tr>
            @endforelse
        </tbody>
    </x-table>
    <div class="mt-4">{{ $subjects->links() }}</div>
</x-card>

{{-- Modal Tambah --}}
<div id="addSubjectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
     x-data @open-add-subject.window="document.getElementById('addSubjectModal').classList.replace('hidden','flex')">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6" @click.stop>
        <h3 class="text-base font-semibold text-gray-800 mb-4">Tambah Mata Pelajaran</h3>
        <form method="POST" action="{{ route('subjects.store') }}" class="space-y-4">
            @csrf
            <x-input label="Nama Mata Pelajaran" name="name" placeholder="Contoh: Matematika" :error="$errors->first('name')" />
            <x-select label="Tingkatan" name="grade_id" :error="$errors->first('grade_id')">
                @foreach($grades as $grade)
                    <option value="{{ $grade->id }}" @selected(old('grade_id') == $grade->id)>{{ $grade->name }}</option>
                @endforeach
            </x-select>
            <div class="flex justify-end gap-2">
                <x-btn variant="outline" type="button"
                    onclick="document.getElementById('addSubjectModal').classList.replace('flex','hidden')">Batal</x-btn>
                <x-btn type="submit">Simpan</x-btn>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="editSubjectModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
     x-data="editSubject()" @open-edit-subject.window="open($event.detail)">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6" @click.stop>
        <h3 class="text-base font-semibold text-gray-800 mb-4">Edit Mata Pelajaran</h3>
        <form method="POST" :action="`/subjects/${id}`" class="space-y-4">
            @csrf @method('PUT')
            <x-input label="Nama Mata Pelajaran" name="name" x-model="name" />
            <div class="space-y-1">
                <label class="block text-sm font-medium text-gray-700">Tingkatan</label>
                <select name="grade_id" x-model="grade_id"
                    class="w-full rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm focus:outline-none focus:ring-2 focus:ring-green-400">
                    @foreach($grades as $grade)
                        <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-2">
                <x-btn variant="outline" type="button" @click="close()">Batal</x-btn>
                <x-btn type="submit">Simpan</x-btn>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editSubject() {
    return {
        id: null, name: '', grade_id: '',
        open(item) { this.id = item.id; this.name = item.name; this.grade_id = item.grade_id; document.getElementById('editSubjectModal').classList.replace('hidden','flex'); },
        close() { document.getElementById('editSubjectModal').classList.replace('flex','hidden'); }
    }
}
</script>
@endpush
@endsection
