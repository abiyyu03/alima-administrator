<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Pupil;
use App\Models\PupilPresence;
use App\Models\SchoolClass;
use App\Models\Tutor;
use App\Models\TutorPresence;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->isTutor()) {
            return $this->tutorDashboard($user);
        }

        return $this->adminDashboard();
    }

    private function tutorDashboard($user)
    {
        $tutor = $user->tutor;
        $now   = Carbon::now();

        if (! $tutor) {
            return view('dashboard.tutor', ['tutor' => null, 'user' => $user]);
        }

        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $now->copy()->endOfMonth();

        // Stat cards
        // Source of truth: tutor_presences.amount (tutor_salaries hanya write-only, dipakai sebagai pembanding)
        $gajiBulanIni = TutorPresence::where('tutor_id', $tutor->id)
            ->where('status', 'presence')
            ->whereHas('classSession', fn($q) =>
                $q->whereBetween('date', [$monthStart, $monthEnd])
            )->sum('amount');

        $gajiTotal = TutorPresence::where('tutor_id', $tutor->id)
            ->where('status', 'presence')->sum('amount');

        $sesiBulanIni = TutorPresence::where('tutor_id', $tutor->id)
            ->where('status', 'presence')
            ->whereHas('classSession', fn($q) =>
                $q->whereBetween('date', [$monthStart, $monthEnd])
            )->count();

        $sesiTotal = TutorPresence::where('tutor_id', $tutor->id)
            ->where('status', 'presence')->count();

        // Grafik penghasilan 10 minggu terakhir
        $weeks  = [];
        $cursor = $now->copy()->subWeeks(9)->startOfWeek(Carbon::MONDAY);
        for ($i = 0; $i < 10; $i++) {
            $wStart = $cursor->copy();
            $wEnd   = $cursor->copy()->endOfWeek(Carbon::SUNDAY);

            $weeks[] = [
                'label' => $wStart->format('d/m'),
                'gaji'  => TutorPresence::where('tutor_id', $tutor->id)
                    ->where('status', 'presence')
                    ->whereHas('classSession', fn($q) =>
                        $q->whereBetween('date', [$wStart, $wEnd])
                    )->sum('amount'),
                'sesi'  => TutorPresence::where('tutor_id', $tutor->id)
                    ->where('status', 'presence')
                    ->whereHas('classSession', fn($q) =>
                        $q->whereBetween('date', [$wStart, $wEnd])
                    )->count(),
            ];
            $cursor->addWeek();
        }

        // Presensi terbaru
        $recentPresences = TutorPresence::where('tutor_id', $tutor->id)
            ->with('classSession.schoolClass.courseType')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Kelas yang diampu
        $classes = $tutor->classes()->with(['grade', 'courseType'])->get();

        return view('dashboard.tutor', compact(
            'user', 'tutor', 'gajiBulanIni', 'gajiTotal',
            'sesiBulanIni', 'sesiTotal', 'weeks', 'recentPresences', 'classes'
        ));
    }

    private function adminDashboard()
    {
        $now        = Carbon::now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd   = $now->copy()->endOfMonth();

        $totalPupils        = Pupil::where('active_status', true)->count();
        $totalTutors        = Tutor::count();
        $totalClasses       = SchoolClass::count();
        $totalSessionsToday = ClassSession::whereDate('date', today())->count();

        $gajiTutorBulanIni = TutorPresence::where('status', 'presence')
            ->whereHas('classSession', fn($q) =>
                $q->whereBetween('date', [$monthStart, $monthEnd])
            )->sum('amount');

        $tagihanDevBulanIni = PupilPresence::where('status', 'presence')
            ->whereHas('classSession', fn($q) =>
                $q->whereBetween('date', [$monthStart, $monthEnd])
                  ->whereHas('schoolClass.courseType', fn($q2) => $q2->where('name', 'Development Class'))
            )
            ->sum('dev_class_rate');

        $weeks  = [];
        $cursor = $now->copy()->subWeeks(9)->startOfWeek(Carbon::MONDAY);
        for ($i = 0; $i < 10; $i++) {
            $wStart = $cursor->copy();
            $wEnd   = $cursor->copy()->endOfWeek(Carbon::SUNDAY);
            $weeks[] = [
                'label' => $wStart->format('d/m'),
                'hadir' => PupilPresence::where('status', 'presence')
                    ->whereHas('classSession', fn($q) => $q->whereBetween('date', [$wStart, $wEnd]))
                    ->count(),
                'sesi'  => ClassSession::whereBetween('date', [$wStart, $wEnd])->count(),
            ];
            $cursor->addWeek();
        }

        $topTutors = TutorPresence::select('tutor_id', DB::raw('SUM(amount) as total'))
            ->where('status', 'presence')
            ->whereHas('classSession', fn($q) =>
                $q->whereBetween('date', [$monthStart, $monthEnd])
            )
            ->with('tutor')
            ->groupBy('tutor_id')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        return view('dashboard.index', compact(
            'totalPupils', 'totalTutors', 'totalClasses', 'totalSessionsToday',
            'gajiTutorBulanIni', 'tagihanDevBulanIni', 'weeks', 'topTutors'
        ));
    }
}
