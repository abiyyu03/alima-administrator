<?php

namespace App\Http\Controllers;

use App\Models\Pupil;
use App\Models\SchoolClass;
use Illuminate\Http\Request;

class PupilController extends Controller
{
    public function index(Request $request)
    {
        $search   = $request->search;
        $classId  = $request->class_id;
        $status   = $request->status;

        $pupils = Pupil::with('schoolClass.grade')
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%"))
            ->when($classId, fn($q) => $q->where('class_id', $classId))
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
            'code'          => 'required|string|max:30|unique:pupils,code',
            'name'          => 'required|string|max:150',
            'dob'           => 'nullable|date',
            'gender'        => 'nullable|in:male,female',
            'class_id'      => 'required|exists:classes,id',
            'active_status' => 'boolean',
        ]);

        Pupil::create($request->only('code', 'name', 'dob', 'gender', 'class_id') + [
            'active_status' => $request->boolean('active_status', true),
        ]);

        return redirect()->route('pupils.index')->with('success', 'Siswa berhasil ditambahkan.');
    }

    public function edit(Pupil $pupil)
    {
        $classes = SchoolClass::with(['grade', 'courseType'])->orderBy('name')->get();

        return view('pupils.edit', compact('pupil', 'classes'));
    }

    public function update(Request $request, Pupil $pupil)
    {
        $request->validate([
            'code'          => 'required|string|max:30|unique:pupils,code,' . $pupil->id,
            'name'          => 'required|string|max:150',
            'dob'           => 'nullable|date',
            'gender'        => 'nullable|in:male,female',
            'class_id'      => 'required|exists:classes,id',
            'active_status' => 'boolean',
        ]);

        $pupil->update($request->only('code', 'name', 'dob', 'gender', 'class_id') + [
            'active_status' => $request->boolean('active_status'),
        ]);

        return redirect()->route('pupils.index')->with('success', 'Data siswa berhasil diperbarui.');
    }

    public function destroy(Pupil $pupil)
    {
        $pupil->delete();

        return back()->with('success', 'Siswa berhasil dihapus.');
    }
}
