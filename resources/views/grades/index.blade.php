@extends('layouts.app')

@section('title', 'Tingkatan')
@section('header', 'Tingkatan (Grade)')
@section('subheader', 'Kelola data tingkatan kelas')

@section('content')
<x-card title="Daftar Tingkatan">
    <x-slot:actions>
        <x-btn x-data @click="$dispatch('open-add-grade')">+ Tambah</x-btn>
    </x-slot:actions>

    <x-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Mata Pelajaran</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Kelas</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($grades as $grade)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-500 text-sm">{{ $grades->firstItem() + $loop->index }}</td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ $grade->name }}</td>
                <td class="px-4 py-3 text-center hidden sm:table-cell"><x-badge color="blue">{{ $grade->subjects_count }}</x-badge></td>
                <td class="px-4 py-3 text-center hidden sm:table-cell"><x-badge color="green">{{ $grade->classes_count }}</x-badge></td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <x-btn size="sm" variant="outline" x-data @click="$dispatch('open-edit-grade', {{ $grade->toJson() }})">Edit</x-btn>
                        <form method="POST" action="{{ route('grades.destroy', $grade) }}" id="del-grade-{{ $grade->id }}">
                            @csrf @method('DELETE')
                        </form>
                        <x-btn type="button" size="sm" variant="danger" x-data
                            @click="$store.deleteConfirm.show('Hapus tingkatan {{ addslashes($grade->name) }}? Tindakan ini tidak dapat dibatalkan.', 'del-grade-{{ $grade->id }}')">Hapus</x-btn>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 text-sm">Belum ada data tingkatan.</td></tr>
            @endforelse
        </tbody>
    </x-table>
    <div class="mt-4">{{ $grades->links() }}</div>
</x-card>

{{-- Modal Tambah --}}
<div id="addGradeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
     x-data @open-add-grade.window="document.getElementById('addGradeModal').classList.replace('hidden','flex')">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6" @click.stop>
        <h3 class="text-base font-semibold text-gray-800 mb-4">Tambah Tingkatan</h3>
        <form method="POST" action="{{ route('grades.store') }}" class="space-y-4">
            @csrf
            <x-input label="Nama Tingkatan" name="name" placeholder="Contoh: SD" :error="$errors->first('name')" autofocus />
            <div class="flex justify-end gap-2">
                <x-btn variant="outline" type="button"
                    onclick="document.getElementById('addGradeModal').classList.replace('flex','hidden')">Batal</x-btn>
                <x-btn type="submit">Simpan</x-btn>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="editGradeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
     x-data="editGrade()" @open-edit-grade.window="open($event.detail)">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6" @click.stop>
        <h3 class="text-base font-semibold text-gray-800 mb-4">Edit Tingkatan</h3>
        <form method="POST" :action="`/grades/${id}`" class="space-y-4">
            @csrf @method('PUT')
            <x-input label="Nama Tingkatan" name="name" x-model="name" />
            <div class="flex justify-end gap-2">
                <x-btn variant="outline" type="button" @click="close()">Batal</x-btn>
                <x-btn type="submit">Simpan</x-btn>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editGrade() {
    return {
        id: null, name: '',
        open(g) { this.id = g.id; this.name = g.name; document.getElementById('editGradeModal').classList.replace('hidden','flex'); },
        close() { document.getElementById('editGradeModal').classList.replace('flex','hidden'); }
    }
}
</script>
@endpush
@endsection
