<?php

namespace App\Http\Controllers;

use App\Models\CourseType;
use Illuminate\Http\Request;

class CourseTypeController extends Controller
{
    public function index()
    {
        $courseTypes = CourseType::withCount('classes')->orderBy('name')->paginate(15);

        return view('course-types.index', compact('courseTypes'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|unique:course_types,name']);

        CourseType::create($request->only('name'));

        return back()->with('success', 'Jenis kursus berhasil ditambahkan.');
    }

    public function update(Request $request, CourseType $courseType)
    {
        $request->validate(['name' => 'required|string|max:100|unique:course_types,name,' . $courseType->id]);

        $courseType->update($request->only('name'));

        return back()->with('success', 'Jenis kursus berhasil diperbarui.');
    }

    public function destroy(CourseType $courseType)
    {
        if ($courseType->classes()->exists()) {
            return back()->with('error', 'Jenis kursus tidak bisa dihapus karena masih digunakan.');
        }

        $courseType->delete();

        return back()->with('success', 'Jenis kursus berhasil dihapus.');
    }
}
