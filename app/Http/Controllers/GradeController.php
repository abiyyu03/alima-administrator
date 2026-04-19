<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index()
    {
        $grades = Grade::withCount(['subjects', 'classes'])->orderBy('name')->paginate(15);

        return view('grades.index', compact('grades'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:grades,name']);

        Grade::create($request->only('name'));

        return back()->with('success', 'Tingkatan berhasil ditambahkan.');
    }

    public function update(Request $request, Grade $grade)
    {
        $request->validate(['name' => 'required|string|max:100|unique:grades,name,' . $grade->id]);

        $grade->update($request->only('name'));

        return back()->with('success', 'Tingkatan berhasil diperbarui.');
    }

    public function destroy(Grade $grade)
    {
        if ($grade->classes()->exists() || $grade->subjects()->exists()) {
            return back()->with('error', 'Tingkatan tidak bisa dihapus karena masih digunakan.');
        }

        $grade->delete();

        return back()->with('success', 'Tingkatan berhasil dihapus.');
    }
}
