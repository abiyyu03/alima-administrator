<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Tutor;
use App\Models\TutorPresence;
use App\Models\TutorSalary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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

        $sessions = ClassSession::with([
                'schoolClass.grade',
                'schoolClass.courseType',
                'tutorPresences.tutor',
                'pupilPresences',
            ])
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
     * Admin updates any tutor presence status.
     */
    public function update(Request $request, TutorPresence $presence)
    {
        $validated = $request->validate([
            'status'       => 'required|in:presence,absent,sick,permission',
            'note'         => 'nullable|string|max:500',
            'material'     => 'nullable|string|max:500',
            'session_date' => 'nullable|date',
            'photo'        => 'nullable|image|max:5120',
        ]);

        $presence->load('classSession.schoolClass.courseType');
        $amount = $validated['status'] === 'presence'
            ? $this->calcAmount($presence->classSession, $presence->tutor_id, $validated['status'])
            : 0;

        $newPhotoPath = null;
        if ($request->hasFile('photo')) {
            $oldPhoto = $presence->classSession->photo_file;
            if ($oldPhoto) {
                Storage::disk('public')->delete($oldPhoto);
            }
            $newPhotoPath = $request->file('photo')->store('session-photos', 'public');
        }

        DB::transaction(function () use ($presence, $validated, $amount, $newPhotoPath) {
            $sessionUpdate = ['material' => $validated['material'] ?? null];
            if (! empty($validated['session_date'])) {
                $sessionUpdate['date'] = $validated['session_date'];
            }
            if ($newPhotoPath !== null) {
                $sessionUpdate['photo_file'] = $newPhotoPath;
            }
            $presence->classSession->update($sessionUpdate);
            $presence->update([
                'status' => $validated['status'],
                'amount' => $amount,
                'note'   => $validated['note'] ?? null,
            ]);

            $this->syncSalary($presence, $amount);
        });

        return back()->with('success', 'Presensi berhasil diperbarui.');
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

        $classes = $tutor->classes()->with([
            'grade',
            'courseType',
            'pupils' => fn($q) => $q->where('active_status', true)->orderBy('name'),
        ])->get();

        $classIds = $classes->pluck('id');
        $sessions = ClassSession::with([
                'schoolClass.courseType',
                'pupilPresences',
                'tutorPresences' => fn($q) => $q->where('tutor_id', $tutor->id),
            ])
            ->whereIn('class_id', $classIds)
            ->whereBetween('date', [$weekStart, $weekEnd])
            ->orderBy('date')
            ->get()
            ->groupBy('class_id');

        $weekPresences = TutorPresence::where('tutor_id', $tutor->id)
            ->whereHas('classSession', fn($q) => $q->whereBetween('date', [$weekStart, $weekEnd]))
            ->get();

        $statsWeek = [
            'total'  => $weekPresences->count(),
            'hadir'  => $weekPresences->where('status', 'presence')->count(),
            'earned' => $weekPresences->sum(fn($p) => $p->earned),
        ];

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
            'class_id'    => 'required|exists:classes,id',
            'date'        => 'required|date',
            'material'    => 'nullable|string|max:500',
            'status'      => 'required|in:presence,absent,sick,permission',
            'note'        => 'nullable|string|max:500',
            'week'        => 'nullable|date',
            'pupil_ids'   => 'nullable|array',
            'pupil_ids.*' => 'exists:pupils,id',
            'photo'       => 'nullable|image|max:5120',
        ]);

        $ownsClass = $tutor->classes()->where('classes.id', $validated['class_id'])->exists();
        if (! $ownsClass) abort(403, 'Kamu tidak mengampu kelas ini.');

        $photoPath = $request->hasFile('photo')
            ? $request->file('photo')->store('session-photos', 'public')
            : null;

        DB::transaction(function () use ($validated, $tutor, $photoPath) {
            $pupilIds = $validated['pupil_ids'] ?? [];

            $session = ClassSession::create([
                'class_id'         => $validated['class_id'],
                'date'             => $validated['date'],
                'material'         => $validated['material'] ?? null,
                'number_of_pupils' => count($pupilIds),
                'photo_file'       => $photoPath,
            ]);

            // Create pupil presences — checked = hadir, all others in class = absent
            $allPupilIds = \App\Models\Pupil::where('class_id', $validated['class_id'])
                ->where('active_status', true)
                ->pluck('id');

            foreach ($allPupilIds as $pid) {
                \App\Models\PupilPresence::create([
                    'class_session_id' => $session->id,
                    'pupil_id'         => $pid,
                    'status'           => in_array($pid, $pupilIds) ? 'presence' : 'absent',
                ]);
            }

            $presence = TutorPresence::create([
                'class_session_id' => $session->id,
                'tutor_id'         => $tutor->id,
                'status'           => $validated['status'],
                'amount'           => 0,
                'note'             => $validated['note'] ?? null,
            ]);

            $amount = $this->calcAmount($session->load('schoolClass.courseType'), $tutor->id, $validated['status']);
            $presence->update(['amount' => $amount]);

            $this->syncSalary($presence, $amount);
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
            'status'       => 'required|in:presence,absent,sick,permission',
            'note'         => 'nullable|string|max:500',
            'material'     => 'nullable|string|max:500',
            'week'         => 'nullable|date',
            'session_date' => 'nullable|date',
            'photo'        => 'nullable|image|max:5120',
        ]);

        $presence->load('classSession.schoolClass.courseType');
        $amount = $validated['status'] === 'presence'
            ? $this->calcAmount($presence->classSession, $tutor->id, $validated['status'])
            : 0;

        $newPhotoPath = null;
        if ($request->hasFile('photo')) {
            $oldPhoto = $presence->classSession->photo_file;
            if ($oldPhoto) {
                Storage::disk('public')->delete($oldPhoto);
            }
            $newPhotoPath = $request->file('photo')->store('session-photos', 'public');
        }

        DB::transaction(function () use ($presence, $validated, $amount, $newPhotoPath) {
            $sessionUpdate = ['material' => $validated['material'] ?? null];
            if ($newPhotoPath !== null) {
                $sessionUpdate['photo_file'] = $newPhotoPath;
            }
            if (! empty($validated['session_date'])) {
                $sessionUpdate['date'] = $validated['session_date'];
            }
            $presence->classSession->update($sessionUpdate);
            $presence->update([
                'status' => $validated['status'],
                'amount' => $amount,
                'note'   => $validated['note'] ?? null,
            ]);

            $this->syncSalary($presence, $amount);
        });

        return redirect()->route('my-presences', ['date' => $validated['week'] ?? null])
            ->with('success', 'Presensi berhasil diperbarui.');
    }

    // ---------------------------------------------------------------

    /**
     * Calculate the tutor amount for a session based on course type.
     * Regular: rate × pupils present. Others: flat rate.
     */
    public static function calcAmount(ClassSession $session, int $tutorId, string $status): float
    {
        if ($status !== 'presence') return 0;

        $rate = Tutor::find($tutorId)?->classes()
            ->where('classes.id', $session->class_id)
            ->first()?->pivot->amount ?? 0;

        $courseTypeName = $session->schoolClass->courseType?->name ?? '';

        if ($courseTypeName === 'Regular') {
            $pupilsHadir = $session->pupilPresences()
                ->where('status', 'presence')
                ->count();

            if ($pupilsHadir === 0) return 0;

            $minPupils = config('presence.regular_min_pupils');
            if ($pupilsHadir < $minPupils) {
                return (float) config('presence.regular_min_incentive');
            }

            return (float) $rate * $pupilsHadir;
        }

        return (float) $rate;
    }

    private function syncSalary(TutorPresence $presence, float $amount): void
    {
        if ($amount > 0) {
            TutorSalary::updateOrCreate(
                ['tutor_presence_id' => $presence->id],
                ['tutor_id' => $presence->tutor_id, 'salary' => $amount]
            );
        } else {
            TutorSalary::where('tutor_presence_id', $presence->id)->delete();
        }
    }
}
