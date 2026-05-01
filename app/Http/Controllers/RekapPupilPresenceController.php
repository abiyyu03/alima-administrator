<?php

namespace App\Http\Controllers;

use App\Models\Pupil;
use App\Models\PupilPresence;
use App\Models\SchoolClass;
use Carbon\Carbon;
use Illuminate\Http\Request;

class RekapPupilPresenceController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->from
            ? Carbon::parse($request->from)->startOfDay()
            : Carbon::now()->startOfMonth();

        $to = $request->to
            ? Carbon::parse($request->to)->endOfDay()
            : Carbon::now()->endOfMonth();

        $classId = $request->class_id;

        $weeks = $this->buildWeeks($from, $to);

        $presences = PupilPresence::with([
                'classSession.schoolClass.courseType',
                'pupil',
            ])
            ->whereHas('classSession', fn($q) => $q->whereBetween('date', [$from, $to]))
            ->when($classId, fn($q) => $q->whereHas('classSession', fn($q2) => $q2->where('class_id', $classId)))
            ->get();

        // Group by pupil
        $pupils = Pupil::with('schoolClass.courseType')
            ->where('active_status', true)
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->orderBy('name')
            ->get();

        $rows = [];

        foreach ($pupils as $pupil) {
            $pupilPresences = $presences->where('pupil_id', $pupil->id);

            if ($pupilPresences->isEmpty()) continue;

            $weekCounts = [];
            foreach ($weeks as $label => $range) {
                $weekCounts[$label] = $pupilPresences->filter(function ($p) use ($range) {
                    $date = Carbon::parse($p->classSession->date);
                    return $date->between($range['from'], $range['to']);
                })->where('status', 'presence')->count();
            }

            $total    = $pupilPresences->count();
            $hadir    = $pupilPresences->where('status', 'presence')->count();
            $absen    = $pupilPresences->whereIn('status', ['absent', 'sick', 'permission'])->count();
            $persen   = $total > 0 ? round($hadir / $total * 100) : 0;

            $rows[] = [
                'pupil'      => $pupil,
                'className'  => $pupil->schoolClass?->name ?? '-',
                'weeks'      => $weekCounts,
                'total'      => $total,
                'hadir'      => $hadir,
                'absen'      => $absen,
                'persen'     => $persen,
            ];
        }

        $classes    = SchoolClass::with('grade')->orderBy('name')->get();
        $weekLabels = array_keys($weeks);

        return view('rekap.pupil', compact('rows', 'weekLabels', 'weeks', 'from', 'to', 'classes', 'classId'));
    }

    private function buildWeeks(Carbon $from, Carbon $to): array
    {
        $weeks  = [];
        $cursor = $from->copy()->startOfWeek(Carbon::MONDAY);
        $week   = 1;

        while ($cursor->lte($to)) {
            $weekEnd        = $cursor->copy()->endOfWeek(Carbon::SUNDAY);
            $weeks['Pekan ' . $week] = [
                'from' => $cursor->copy()->max($from),
                'to'   => $weekEnd->copy()->min($to),
            ];
            $cursor->addWeek();
            $week++;
        }

        return $weeks;
    }
}
