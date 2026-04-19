<?php

namespace App\Http\Controllers;

use App\Models\Tutor;
use Illuminate\Http\Request;

class TutorSalaryController extends Controller
{
    public function index(Request $request)
    {
        $tutors = Tutor::with([
            'classes.grade',
            'classes.courseType',
        ])
        ->withCount('classes')
        ->orderBy('name')
        ->get()
        ->map(function ($tutor) {
            $tutor->total_rate = $tutor->classes->sum('pivot.amount');

            return $tutor;
        });

        return view('tutor-salaries.index', compact('tutors'));
    }
}
