@extends('layouts.app')

@section('title', 'Jenis Kursus')
@section('header', 'Jenis Kursus')
@section('subheader', 'Kelola data jenis kursus')

@section('content')
<x-card title="Daftar Jenis Kursus">
    <x-slot:actions>
        <x-btn x-data @click="$dispatch('open-add-course-type')">+ Tambah</x-btn>
    </x-slot:actions>

    <x-table>
        <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">#</th>
                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Nama</th>
                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 uppercase hidden sm:table-cell">Kelas</th>
                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
            @forelse($courseTypes as $type)
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3 text-gray-500 text-sm">{{ $courseTypes->firstItem() + $loop->index }}</td>
                <td class="px-4 py-3 font-medium text-gray-800">{{ $type->name }}</td>
                <td class="px-4 py-3 text-center hidden sm:table-cell"><x-badge color="green">{{ $type->classes_count }}</x-badge></td>
                <td class="px-4 py-3 text-right">
                    <div class="flex items-center justify-end gap-2">
                        <x-btn size="sm" variant="outline" x-data @click="$dispatch('open-edit-course-type', {{ $type->toJson() }})">Edit</x-btn>
                        <form method="POST" action="{{ route('course-types.destroy', $type) }}"
                            onsubmit="return confirm('Hapus jenis kursus {{ $type->name }}?')">
                            @csrf @method('DELETE')
                            <x-btn type="submit" size="sm" variant="danger">Hapus</x-btn>
                        </form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="4" class="px-4 py-8 text-center text-gray-400 text-sm">Belum ada data jenis kursus.</td></tr>
            @endforelse
        </tbody>
    </x-table>
    <div class="mt-4">{{ $courseTypes->links() }}</div>
</x-card>

{{-- Modal Tambah --}}
<div id="addCourseTypeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
     x-data @open-add-course-type.window="document.getElementById('addCourseTypeModal').classList.replace('hidden','flex')">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6" @click.stop>
        <h3 class="text-base font-semibold text-gray-800 mb-4">Tambah Jenis Kursus</h3>
        <form method="POST" action="{{ route('course-types.store') }}" class="space-y-4">
            @csrf
            <x-input label="Nama Jenis Kursus" name="name" placeholder="Contoh: Regular" :error="$errors->first('name')" />
            <div class="flex justify-end gap-2">
                <x-btn variant="outline" type="button"
                    onclick="document.getElementById('addCourseTypeModal').classList.replace('flex','hidden')">Batal</x-btn>
                <x-btn type="submit">Simpan</x-btn>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="editCourseTypeModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4"
     x-data="editCourseType()" @open-edit-course-type.window="open($event.detail)">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-sm p-6" @click.stop>
        <h3 class="text-base font-semibold text-gray-800 mb-4">Edit Jenis Kursus</h3>
        <form method="POST" :action="`/course-types/${id}`" class="space-y-4">
            @csrf @method('PUT')
            <x-input label="Nama Jenis Kursus" name="name" x-model="name" />
            <div class="flex justify-end gap-2">
                <x-btn variant="outline" type="button" @click="close()">Batal</x-btn>
                <x-btn type="submit">Simpan</x-btn>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function editCourseType() {
    return {
        id: null, name: '',
        open(item) { this.id = item.id; this.name = item.name; document.getElementById('editCourseTypeModal').classList.replace('hidden','flex'); },
        close() { document.getElementById('editCourseTypeModal').classList.replace('flex','hidden'); }
    }
}
</script>
@endpush
@endsection
