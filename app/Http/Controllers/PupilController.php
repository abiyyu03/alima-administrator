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
        $classes = SchoolClass::with(['grade', 'courseType'])->orderBy('name')->get();

        return view('pupils.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'code'           => 'required|string|max:30|unique:pupils,code',
            'name'           => 'required|string|max:150',
            'dob'            => 'nullable|date',
            'gender'         => 'nullable|in:male,female',
            'class_ids'      => 'required|array|min:1',
            'class_ids.*'    => 'exists:classes,id',
            'class_rates'    => 'nullable|array',
            'class_rates.*'  => 'nullable|integer|min:0',
            'active_status'  => 'boolean',
            'dev_class_rate' => 'nullable|integer|min:0',
        ]);

        $pupil = Pupil::create([
            'code'           => $request->code,
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
        $pivotRates  = $pupil->classes()->get()->pluck('pivot.rate', 'id')->toArray();

        return view('pupils.edit', compact('pupil', 'classes', 'selectedIds', 'pivotRates'));
    }

    public function update(Request $request, Pupil $pupil)
    {
        $request->validate([
            'code'           => 'required|string|max:30|unique:pupils,code,' . $pupil->id,
            'name'           => 'required|string|max:150',
            'dob'            => 'nullable|date',
            'gender'         => 'nullable|in:male,female',
            'class_ids'      => 'required|array|min:1',
            'class_ids.*'    => 'exists:classes,id',
            'class_rates'    => 'nullable|array',
            'class_rates.*'  => 'nullable|integer|min:0',
            'active_status'  => 'boolean',
            'dev_class_rate' => 'nullable|integer|min:0',
        ]);

        $pupil->update([
            'code'           => $request->code,
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
        $pupil->delete();

        return back()->with('success', 'Siswa berhasil dihapus.');
    }

    private function buildSyncData(Request $request): array
    {
        $rates = $request->input('class_rates', []);
        $sync  = [];
        foreach ($request->class_ids as $classId) {
            $sync[$classId] = ['rate' => (int) ($rates[$classId] ?? 0)];
        }
        return $sync;
    }
}
