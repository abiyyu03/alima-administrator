<?php

namespace App\Http\Controllers;

use App\Models\Pupil;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class PupilController extends Controller
{
    public function index(Request $request)
    {
        $search  = $request->search;
        $classId = $request->class_id;
        $status  = $request->status;

        $pupils = Pupil::with(['classes.grade'])
            ->withCount([
                'presences as total_sesi',
            ])
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%"))
            ->when($classId, fn($q) => $q->whereHas('classes', fn($q2) => $q2->where('classes.id', $classId)))
            ->when($status !== null && $status !== '', fn($q) => $q->where('active_status', (bool) $status))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $classes = SchoolClass::with('grade')->orderBy('name')->get();

        return view('pupils.index', compact('pupils', 'classes', 'search', 'classId', 'status'));
    }

    public function create()
    {
        $classes      = SchoolClass::with(['grade', 'courseType'])->orderBy('name')->get();
        $previewCode  = $this->generateCode();

        return view('pupils.create', compact('classes', 'previewCode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:150',
            'dob'            => 'nullable|date',
            'gender'         => 'nullable|in:male,female',
            'class_ids'      => 'required|array|min:1',
            'class_ids.*'    => 'exists:classes,id',
            'active_status'  => 'boolean',
            'dev_class_rate' => 'nullable|integer|min:0',
        ]);

        $pupil = Pupil::create([
            'code'           => $this->generateCode(),
            'name'           => $request->name,
            'dob'            => $request->dob,
            'gender'         => $request->gender,
            'active_status'  => $request->boolean('active_status', true),
            'dev_class_rate' => (int) $request->input('dev_class_rate', 0),
        ]);

        $pupil->classes()->sync($this->buildSyncData($request));

        return redirect()->route('pupils.index')->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function edit(Pupil $pupil)
    {
        $classes     = SchoolClass::with(['grade', 'courseType'])->orderBy('name')->get();
        $selectedIds = $pupil->classes()->pluck('classes.id')->toArray();

        return view('pupils.edit', compact('pupil', 'classes', 'selectedIds'));
    }

    public function update(Request $request, Pupil $pupil)
    {
        $request->validate([
            'name'           => 'required|string|max:150',
            'dob'            => 'nullable|date',
            'gender'         => 'nullable|in:male,female',
            'class_ids'      => 'required|array|min:1',
            'class_ids.*'    => 'exists:classes,id',
            'active_status'  => 'boolean',
            'dev_class_rate' => 'nullable|integer|min:0',
        ]);

        $pupil->update([
            'name'           => $request->name,
            'dob'            => $request->dob,
            'gender'         => $request->gender,
            'active_status'  => $request->boolean('active_status'),
            'dev_class_rate' => (int) $request->input('dev_class_rate', 0),
        ]);

        $pupil->classes()->sync($this->buildSyncData($request));

        return redirect()->route('pupils.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Pupil $pupil)
    {
        // Cegah hapus anak yang masih punya riwayat: hindari sesi/presensi yatim (FK rusak).
        $hasPresences = $pupil->presences()->exists();
        $hasSessions  = \App\Models\ClassSession::where('pupil_id', $pupil->id)->exists();

        if ($hasPresences || $hasSessions) {
            return back()->with('error', 'Siswa tidak dapat dihapus karena masih memiliki riwayat sesi/presensi. Nonaktifkan siswa ini sebagai gantinya.');
        }

        $pupil->delete();

        return back()->with('success', 'Siswa berhasil dihapus.');
    }

    private function generateCode(): string
    {
        $prefix = now()->format('Ymd');

        // Count pupils registered on the same date prefix
        $count = Pupil::where('code', 'like', $prefix . '%')->count();

        do {
            $count++;
            $code = $prefix . str_pad($count, 3, '0', STR_PAD_LEFT);
        } while (Pupil::where('code', $code)->exists());

        return $code;
    }

    private function buildSyncData(Request $request): array
    {
        return array_fill_keys($request->class_ids, []);
    }
}
