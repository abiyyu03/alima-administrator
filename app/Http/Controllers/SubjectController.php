<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        $subjects = Subject::with('grade')->orderBy('name')->paginate(15);
        $grades   = Grade::orderBy('name')->get();

        return view('subjects.index', compact('subjects', 'grades'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:150',
            'grade_id' => 'required|exists:grades,id',
        ]);

        Subject::create($request->only('name', 'grade_id'));

        return back()->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function update(Request $request, Subject $subject)
    {
        $request->validate([
            'name'     => 'required|string|max:150',
            'grade_id' => 'required|exists:grades,id',
        ]);

        $subject->update($request->only('name', 'grade_id'));

        return back()->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Subject $subject)
    {
        $subject->delete();

        return back()->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
