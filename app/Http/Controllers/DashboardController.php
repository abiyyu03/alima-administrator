<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Pupil;
use App\Models\SchoolClass;
use App\Models\Tutor;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index', [
            'totalPupils'        => Pupil::where('active_status', true)->count(),
            'totalTutors'        => Tutor::count(),
            'totalClasses'       => SchoolClass::count(),
            'totalSessionsToday' => ClassSession::whereDate('date', today())->count(),
        ]);
    }
}
