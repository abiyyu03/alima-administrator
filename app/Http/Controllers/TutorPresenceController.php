<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Tutor;
use App\Models\TutorPresence;
use App\Models\TutorSalary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TutorPresenceController extends Controller
{
    /**
     * Admin view: all tutor presences for a given week.
     */
    public function index(Request $request)
    {
        $tutorId  = $request->tutor_id;
        $dateFrom = $request->date_from;
        $dateTo   = $request->date_to;

        // Mode: custom range atau week navigator
        if ($dateFrom || $dateTo) {
            $weekStart = $dateFrom ? Carbon::parse($dateFrom)->startOfDay() : Carbon::now()->startOfWeek(Carbon::MONDAY);
            $weekEnd   = $dateTo   ? Carbon::parse($dateTo)->endOfDay()     : $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        } else {
            $weekStart = $request->date
                ? Carbon::parse($request->date)->startOfWeek(Carbon::MONDAY)
                : Carbon::now()->startOfWeek(Carbon::MONDAY);
            $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);
        }

        $tutors = Tutor::orderBy('name')->get();

        $sessions = ClassSession::with(['schoolClass.grade', 'schoolClass.courseType', 'tutorPresences.tutor'])
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->when($tutorId, fn($q) => $q->whereHas('tutorPresences', fn($q2) => $q2->where('tutor_id', $tutorId)))
            ->orderBy('date')
            ->get();

        if ($tutorId) {
            $sessions->each(function ($session) use ($tutorId) {
                $session->setRelation(
                    'tutorPresences',
                    $session->tutorPresences->where('tutor_id', $tutorId)->values()
                );
            });
        }

        return view('tutor-presences.index', compact(
            'tutors', 'sessions', 'weekStart', 'weekEnd', 'tutorId', 'dateFrom', 'dateTo'
        ));
    }

    /**
     * Tutor self-service: view own weekly presences & fill attendance.
     */
    public function myPresences(Request $request)
    {
        $user = auth()->user();
        $tutor = $user->tutor;

        if (! $tutor) {
            return redirect()->route('dashboard')
                ->with('error', 'Akun kamu belum terhubung ke data tutor. Hubungi admin.');
        }

        $weekStart = $request->date
            ? Carbon::parse($request->date)->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        // Kelas yang diampu tutor ini
        $classes = $tutor->classes()->with(['grade', 'courseType'])->get();

        // Session minggu ini untuk kelas-kelas tutor
        $classIds = $classes->pluck('id');
        $sessions = ClassSession::with(['tutorPresences' => fn($q) => $q->where('tutor_id', $tutor->id)])
            ->whereIn('class_id', $classIds)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->orderBy('date')
            ->get()
            ->groupBy('class_id');

        // Summary stats (all time & this week)
        $weekPresences = TutorPresence::where('tutor_id', $tutor->id)
            ->whereHas('classSession', fn($q) => $q->whereBetween('date', [$weekStart, $weekEnd]))
            ->get();

        $statsWeek = [
            'total'    => $weekPresences->count(),
            'hadir'    => $weekPresences->where('status', 'presence')->count(),
            'earned'   => $weekPresences->sum(fn($p) => $p->earned),
        ];

        // Riwayat 4 minggu terakhir (incl. current)
        $history = TutorPresence::where('tutor_id', $tutor->id)
            ->with(['classSession.schoolClass.grade'])
            ->whereHas('classSession', fn($q) => $q->where('date', '>=', now()->subWeeks(4)->startOfWeek()))
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(fn($p) => Carbon::parse($p->classSession->date)->startOfWeek(Carbon::MONDAY)->format('Y-m-d'));

        return view('tutor-presences.my', compact(
            'tutor', 'classes', 'sessions', 'weekStart', 'weekEnd', 'statsWeek', 'history'
        ));
    }

    /**
     * Tutor creates a new session for their class + marks their own presence.
     */
    public function storeMySession(Request $request)
    {
        $user = auth()->user();
        $tutor = $user->tutor;

        if (! $tutor) abort(403);

        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'date'     => 'required|date',
            'material' => 'nullable|string|max:500',
            'status'   => 'required|in:presence,absent,sick,permission',
            'week'     => 'nullable|date',
        ]);

        // Pastikan class ini memang diampu tutor ini
        $ownsClass = $tutor->classes()->where('classes.id', $validated['class_id'])->exists();
        if (! $ownsClass) abort(403, 'Kamu tidak mengampu kelas ini.');

        DB::transaction(function () use ($validated, $tutor) {
            $session = ClassSession::create([
                'class_id' => $validated['class_id'],
                'date'     => $validated['date'],
                'material' => $validated['material'] ?? null,
                'number_of_pupils' => 0,
            ]);

            $amount = $tutor->classes()
                ->where('classes.id', $validated['class_id'])
                ->first()?->pivot->amount ?? 0;

            $presence = TutorPresence::create([
                'class_session_id' => $session->id,
                'tutor_id'         => $tutor->id,
                'status'           => $validated['status'],
                'amount'           => $validated['status'] === 'presence' ? $amount : 0,
            ]);

            // Auto-create salary record jika hadir
            if ($validated['status'] === 'presence' && $amount > 0) {
                TutorSalary::create([
                    'tutor_id'           => $tutor->id,
                    'tutor_presence_id'  => $presence->id,
                    'salary'             => $amount,
                ]);
            }
        });

        return redirect()->route('my-presences', ['date' => $validated['week'] ?? $validated['date']])
            ->with('success', 'Presensi berhasil dicatat.');
    }

    /**
     * Tutor updates their own presence status.
     */
    public function updateMyPresence(Request $request, TutorPresence $presence)
    {
        $user = auth()->user();
        $tutor = $user->tutor;

        if (! $tutor || $presence->tutor_id !== $tutor->id) abort(403);

        $validated = $request->validate([
            'status' => 'required|in:presence,absent,sick,permission',
            'week'   => 'nullable|date',
        ]);

        $amount = 0;
        if ($validated['status'] === 'presence') {
            $classId = $presence->classSession->class_id;
            $amount = $tutor->classes()->where('classes.id', $classId)->first()?->pivot->amount ?? 0;
        }

        DB::transaction(function () use ($presence, $validated, $tutor, $amount) {
            $presence->update([
                'status' => $validated['status'],
                'amount' => $amount,
            ]);

            // Update salary record
            if ($validated['status'] === 'presence' && $amount > 0) {
                TutorSalary::updateOrCreate(
                    ['tutor_presence_id' => $presence->id],
                    ['tutor_id' => $tutor->id, 'salary' => $amount]
                );
            } else {
                TutorSalary::where('tutor_presence_id', $presence->id)->delete();
            }
        });

        return redirect()->route('my-presences', ['date' => $validated['week'] ?? null])
            ->with('success', 'Status presensi diperbarui.');
    }
}
