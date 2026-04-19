<?php

namespace App\Http\Controllers;

use App\Models\CourseType;
use App\Models\Grade;
use App\Models\SchoolClass;
use App\Models\Subject;
use Illuminate\Http\Request;

class SchoolClassController extends Controller
{
    public function index()
    {
        $classes = SchoolClass::with(['grade', 'courseType', 'subject'])
            ->withCount('pupils')
            ->orderBy('name')
            ->paginate(15);

        return view('classes.index', compact('classes'));
    }

    public function create()
    {
        $grades      = Grade::orderBy('name')->get();
        $courseTypes = CourseType::orderBy('name')->get();
        $subjects    = Subject::with('grade')->orderBy('name')->get();

        return view('classes.create', compact('grades', 'courseTypes', 'subjects'));
    }

    public function store(Request $request)
    {
        $isPrivate = $this->isPrivateCourseType($request->course_type_id);

        $request->validate([
            'name'           => 'required|string|max:150',
            'grade_id'       => 'required|exists:grades,id',
            'course_type_id' => 'required|exists:course_types,id',
            'subject_id'     => $isPrivate ? 'required|exists:subjects,id' : 'nullable|exists:subjects,id',
        ]);

        SchoolClass::create([
            'name'           => $request->name,
            'grade_id'       => $request->grade_id,
            'course_type_id' => $request->course_type_id,
            'subject_id'     => $isPrivate ? $request->subject_id : null,
        ]);

        return redirect()->route('classes.index')->with('success', 'Kelas berhasil ditambahkan.');
    }

    public function edit(SchoolClass $class)
    {
        $grades      = Grade::orderBy('name')->get();
        $courseTypes = CourseType::orderBy('name')->get();
        $subjects    = Subject::with('grade')->orderBy('name')->get();

        return view('classes.edit', compact('class', 'grades', 'courseTypes', 'subjects'));
    }

    public function update(Request $request, SchoolClass $class)
    {
        $isPrivate = $this->isPrivateCourseType($request->course_type_id);

        $request->validate([
            'name'           => 'required|string|max:150',
            'grade_id'       => 'required|exists:grades,id',
            'course_type_id' => 'required|exists:course_types,id',
            'subject_id'     => $isPrivate ? 'required|exists:subjects,id' : 'nullable|exists:subjects,id',
        ]);

        $class->update([
            'name'           => $request->name,
            'grade_id'       => $request->grade_id,
            'course_type_id' => $request->course_type_id,
            'subject_id'     => $isPrivate ? $request->subject_id : null,
        ]);

        return redirect()->route('classes.index')->with('success', 'Kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $class)
    {
        if ($class->pupils()->exists()) {
            return back()->with('error', 'Kelas tidak bisa dihapus karena masih memiliki siswa.');
        }

        $class->delete();

        return back()->with('success', 'Kelas berhasil dihapus.');
    }

    private function isPrivateCourseType(?string $courseTypeId): bool
    {
        if (! $courseTypeId) return false;

        return CourseType::where('id', $courseTypeId)
            ->where('name', 'private')
            ->exists();
    }
}
