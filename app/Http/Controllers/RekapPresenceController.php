<?php

namespace App\Http\Controllers;

use App\Models\Tutor;
use App\Models\TutorPresence;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;

class RekapPresenceController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->from
            ? Carbon::parse($request->from)->startOfDay()
            : Carbon::now()->startOfMonth();

        $to = $request->to
            ? Carbon::parse($request->to)->endOfDay()
            : Carbon::now()->endOfMonth();

        // Build week buckets: split date range into weeks (Mon–Sun chunks, clipped to range)
        $weeks = $this->buildWeeks($from, $to);

        // Load all presences in range with needed relations
        $presences = TutorPresence::with([
                'classSession.schoolClass.courseType',
                'classSession.pupilPresences',
                'tutor',
            ])
            ->whereHas('classSession', fn($q) => $q->whereBetween('date', [$from, $to]))
            ->where('status', 'presence')
            ->get();

        // Group by tutor, then by course type
        $tutors = Tutor::orderBy('name')->get();

        $rows = [];

        foreach ($tutors as $tutor) {
            $tutorPresences = $presences->where('tutor_id', $tutor->id);

            if ($tutorPresences->isEmpty()) continue;

            $regular = $this->buildRow($tutorPresences, 'Regular', $weeks);
            $private = $this->buildRow($tutorPresences, 'Private', $weeks);

            if ($regular || $private) {
                $rows[] = [
                    'tutor'   => $tutor,
                    'regular' => $regular,
                    'private' => $private,
                ];
            }
        }

        $weekLabels = array_keys($weeks);

        return view('rekap.presence', compact('rows', 'weekLabels', 'weeks', 'from', 'to'));
    }

    private function buildWeeks(Carbon $from, Carbon $to): array
    {
        $weeks = [];
        $cursor = $from->copy()->startOfWeek(Carbon::MONDAY);
        $weekNum = 1;

        while ($cursor->lte($to)) {
            $weekEnd = $cursor->copy()->endOfWeek(Carbon::SUNDAY);
            $label = 'Pekan ' . $weekNum;
            $weeks[$label] = [
                'from' => $cursor->copy()->max($from),
                'to'   => $weekEnd->copy()->min($to),
            ];
            $cursor->addWeek();
            $weekNum++;
        }

        return $weeks;
    }

    private function buildRow($tutorPresences, string $courseTypeName, array $weeks): ?array
    {
        $filtered = $tutorPresences->filter(
            fn($p) => strtolower($p->classSession->schoolClass->courseType?->name ?? '') === strtolower($courseTypeName)
        );

        if ($filtered->isEmpty()) return null;

        $weekCounts = [];
        foreach ($weeks as $label => $range) {
            $weekCounts[$label] = $filtered->filter(function ($p) use ($range) {
                $date = Carbon::parse($p->classSession->date);
                return $date->between($range['from'], $range['to']);
            })->count();
        }

        $total = array_sum($weekCounts);
        $gaji  = $filtered->sum('amount');

        return [
            'weeks' => $weekCounts,
            'total' => $total,
            'gaji'  => $gaji,
        ];
    }
}
