<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\SchoolClass;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClassSessionController extends Controller
{
    public function index(Request $request)
    {
        $weekStart = $request->date
            ? Carbon::parse($request->date)->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $sessions = ClassSession::with(['schoolClass.grade', 'schoolClass.courseType', 'tutorPresences.tutor'])
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->orderBy('date')
            ->get()
            ->groupBy('class_id');

        $classes = SchoolClass::with(['grade', 'courseType'])->orderBy('name')->get();

        return view('class-sessions.index', compact('sessions', 'classes', 'weekStart', 'weekEnd'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id'         => 'required|exists:classes,id',
            'date'             => 'required|date',
            'material'         => 'nullable|string|max:500',
            'number_of_pupils' => 'nullable|integer|min:0',
            'week'             => 'nullable|date',
        ]);

        ClassSession::create([
            'class_id'         => $validated['class_id'],
            'date'             => $validated['date'],
            'material'         => $validated['material'] ?? null,
            'number_of_pupils' => $validated['number_of_pupils'] ?? 0,
        ]);

        return redirect()->route('class-sessions.index', ['date' => $validated['week'] ?? $validated['date']])
            ->with('success', 'Sesi berhasil ditambahkan.');
    }

    public function destroy(ClassSession $classSession)
    {
        $week = $classSession->date->format('Y-m-d');
        $classSession->delete();

        return redirect()->route('class-sessions.index', ['date' => $week])
            ->with('success', 'Sesi berhasil dihapus.');
    }
}
