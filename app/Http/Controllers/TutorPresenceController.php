<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Tutor;
use App\Models\TutorPresence;
use App\Models\TutorSalary;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'schoolClass.pupils' => fn($q) => $q->where('active_status', true)->orderBy('name'),
            'pupil',
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

        // Precompute per-sesi (config dibaca sekali, bukan per baris di view)
        $minPupils = (int) config('presence.regular_min_pupils');
        $sessions->each(function ($session) use ($minPupils) {
            $isRegular  = strtolower($session->schoolClass->courseType?->name ?? '') === 'regular';
            $present    = $session->pupilPresences->where('status', 'presence');
            $pupilHadir = $present->count();

            $session->is_regular        = $isRegular;
            $session->pupil_hadir       = $pupilHadir;
            $session->min_pupils        = $minPupils;
            $session->is_below_min      = $isRegular && $pupilHadir < $minPupils;
            $session->present_pupil_ids = $present->pluck('pupil_id')->values();
        });

        return view('tutor-presences.index', compact(
            'tutors',
            'sessions',
            'weekStart',
            'weekEnd',
            'tutorId',
            'dateFrom',
            'dateTo'
        ));
    }

    /**
     * Admin updates any tutor presence status.
     */
    public function update(Request $request, TutorPresence $presence)
    {
        $validated = $request->validate([
            'note'         => 'nullable|string|max:500',
            'material'     => 'nullable|string|max:500',
            'session_date' => 'nullable|date',
            'photo'        => 'nullable|image|max:6144',
            'pupil_ids'    => 'nullable|array',
            'pupil_ids.*'  => 'exists:pupils,id',
        ]);

        $validated['status'] = 'presence';

        $presence->load('classSession.schoolClass.courseType');
        $rate      = self::getRate($presence->classSession, $presence->tutor_id);
        $isPrivate = strtolower($presence->classSession->schoolClass->courseType?->name ?? '') === 'private';

        // Sesi private yang anaknya sudah dihapus: jangan lanjut (insert pupil_presences akan langgar FK)
        if ($isPrivate
            && $presence->classSession->pupil_id
            && ! \App\Models\Pupil::whereKey($presence->classSession->pupil_id)->exists()
        ) {
            return back()->with('error', 'Data murid untuk sesi ini sudah tidak tersedia. Silakan hubungi admin untuk memeriksa data siswa.');
        }

        DB::transaction(function () use ($presence, $validated, $rate, $request, $isPrivate) {
            $sessionUpdate = ['material' => $validated['material'] ?? null];
            if (! empty($validated['session_date'])) {
                $sessionUpdate['date'] = $validated['session_date'];
            }
            if ($request->hasFile('photo')) {
                $oldPhoto = $presence->classSession->photo_file;
                if ($oldPhoto) Storage::disk('public')->delete($oldPhoto);
                $path = $request->file('photo')->store('session-photos', 'public');
                $this->compressPhoto($path);
                $sessionUpdate['photo_file'] = preg_replace('/\.[^.]+$/', '.jpg', $path);
            }

            if ($isPrivate) {
                // Sesi private = satu anak; jaga presensi anak tsb tetap hadir
                if ($presence->classSession->pupil_id) {
                    \App\Models\PupilPresence::updateOrCreate(
                        ['class_session_id' => $presence->classSession->id, 'pupil_id' => $presence->classSession->pupil_id],
                        ['status' => 'presence']
                    );
                    $sessionUpdate['number_of_pupils'] = 1;
                }
            } else {
                // Regular: presensi per anak dari pilihan
                $pupilIds = $validated['pupil_ids'] ?? null;
                $this->syncPupilPresences($presence->classSession, $pupilIds);
                $sessionUpdate['number_of_pupils'] = $presence->classSession->pupilPresences()->where('status', 'presence')->count();
            }

            $presence->classSession->update($sessionUpdate);

            $amount = $this->calcAmount($presence->classSession->fresh(), $presence->tutor_id, 'presence');
            $presence->update([
                'status' => 'presence',
                'amount' => $amount,
                'rate'   => $rate,
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
        $user = Auth::user();
        $tutor = $user->tutor;

        if (! $tutor) {
            return redirect()->route('dashboard')
                ->with('error', 'Akun kamu belum terhubung ke data tutor. Hubungi admin.');
        }

        $weekStart = $request->date
            ? Carbon::parse($request->date)->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);

        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        // Rate config dibaca sekali di sini, bukan berulang di dalam loop view
        $minPupils    = (int) config('presence.regular_min_pupils');
        $minIncentive = (int) config('presence.regular_min_incentive');
        $ratePrivate  = (int) config('presence.tutor_rate_private');
        $rateRegular  = (int) config('presence.tutor_rate_regular');

        $classes = $tutor->classes()->with([
            'grade',
            'courseType',
            'pupils' => fn($q) => $q->where('active_status', true)->orderBy('name'),
        ])->get()
            ->each(function ($class) use ($minPupils, $minIncentive, $ratePrivate, $rateRegular) {
                $isRegular = strtolower($class->courseType->name) === 'regular';
                $pivotAmt  = (int) ($class->pivot->amount ?? 0);

                $class->is_regular     = $isRegular;
                $class->extra_fee      = (int) ($class->pivot->extra_fee ?? 0);
                $class->effective_rate = $pivotAmt > 0 ? $pivotAmt : ($isRegular ? $rateRegular : $ratePrivate);
                $class->min_pupils     = $minPupils;
                $class->min_incentive  = $minIncentive;
            });

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
            ->each(function ($session) {
                $present = $session->pupilPresences->where('status', 'presence');
                $session->pupil_hadir       = $present->count();
                $session->pupil_total       = $session->pupilPresences->count();
                $session->present_pupil_ids = $present->pluck('pupil_id')->values();
            })
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

        // Ringkasan per minggu dihitung sekali, supaya view tidak agregasi ulang
        $historySummary = $history->map(fn($presences) => [
            'count'  => $presences->count(),
            'hadir'  => $presences->where('status', 'presence')->count(),
            'earned' => $presences->sum(fn($p) => $p->earned),
        ]);

        // Anak private yang dipegang tutor ini, beserta extra rate per anak
        $assignedPupils   = $tutor->pupils()->get();
        $assignedPupilIds = $assignedPupils->pluck('id');
        $pupilExtraById   = $assignedPupils->pluck('pivot.extra_fee', 'id');

        // Bangun kartu: Regular = 1 kartu per kelas; Private = 1 kartu per anak yang dipegang
        $cards = collect();
        foreach ($classes as $class) {
            $classSessions = $sessions->get($class->id, collect());

            if ($class->is_regular) {
                $cards->push((object) [
                    'class'    => $class,
                    'pupil'    => null,
                    'rate'     => $class->effective_rate,
                    'sessions' => $classSessions,
                ]);
            } else {
                foreach ($class->pupils as $pupil) {
                    if (! $assignedPupilIds->contains($pupil->id)) continue;

                    $cards->push((object) [
                        'class'    => $class,
                        'pupil'    => $pupil,
                        // Private: gaji dasar kelas + extra rate khusus anak ini
                        'rate'     => $class->effective_rate + (int) ($pupilExtraById[$pupil->id] ?? 0),
                        'sessions' => $classSessions->where('pupil_id', $pupil->id)->values(),
                    ]);
                }
            }
        }

        return view('tutor-presences.my', compact(
            'tutor',
            'cards',
            'weekStart',
            'weekEnd',
            'statsWeek',
            'history',
            'historySummary'
        ));
    }

    /**
     * Tutor creates a new session for their class + marks their own presence.
     */
    public function storeMySession(Request $request)
    {
        $user = Auth::user();
        $tutor = $user->tutor;

        if (! $tutor) abort(403);

        $validated = $request->validate([
            'class_id'    => 'required|exists:classes,id',
            'date'        => 'required|date',
            'material'    => 'nullable|string|max:500',
            'note'        => 'nullable|string|max:500',
            'week'        => 'nullable|date',
            'pupil_id'    => 'nullable|exists:pupils,id',
            'pupil_ids'   => 'nullable|array',
            'pupil_ids.*' => 'exists:pupils,id',
            'photo'       => 'nullable|image|max:5120',
        ]);

        $validated['status'] = 'presence';

        $ownsClass = $tutor->classes()->where('classes.id', $validated['class_id'])->exists();
        if (! $ownsClass) abort(403, 'Kamu tidak mengampu kelas ini.');

        $schoolClass = \App\Models\SchoolClass::with('courseType')->find($validated['class_id']);
        $isPrivate   = strtolower($schoolClass->courseType?->name ?? '') === 'private';

        // Private: sesi milik satu anak — wajib pupil_id, anak terdaftar di kelas & dipegang tutor ini
        if ($isPrivate) {
            $request->validate(['pupil_id' => 'required|exists:pupils,id']);
            $enrolled = \App\Models\Pupil::where('id', $validated['pupil_id'])
                ->whereHas('classes', fn($q) => $q->where('classes.id', $validated['class_id']))
                ->exists();
            if (! $enrolled) abort(422, 'Siswa tidak terdaftar di kelas ini.');

            if (! $tutor->pupils()->where('pupils.id', $validated['pupil_id'])->exists()) {
                abort(403, 'Kamu tidak memegang siswa ini.');
            }
        }

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('session-photos', 'public');
            $this->compressPhoto($photoPath);
            $photoPath = preg_replace('/\.[^.]+$/', '.jpg', $photoPath);
        }

        DB::transaction(function () use ($validated, $tutor, $photoPath, $isPrivate) {
            if ($isPrivate) {
                // Satu sesi = satu anak; anak otomatis hadir
                $pupilId = (int) $validated['pupil_id'];

                $session = ClassSession::create([
                    'class_id'         => $validated['class_id'],
                    'pupil_id'         => $pupilId,
                    'date'             => $validated['date'],
                    'material'         => $validated['material'] ?? null,
                    'number_of_pupils' => 1,
                    'photo_file'       => $photoPath,
                ]);

                \App\Models\PupilPresence::create([
                    'class_session_id' => $session->id,
                    'pupil_id'         => $pupilId,
                    'status'           => 'presence',
                ]);
            } else {
                $allPupilIds = \App\Models\Pupil::whereHas('classes', fn($q) => $q->where('classes.id', $validated['class_id']))
                    ->where('active_status', true)
                    ->pluck('id');

                // Regular: presensi per anak dari pilihan tutor
                $pupilIds = $validated['pupil_ids'] ?? [];

                $session = ClassSession::create([
                    'class_id'         => $validated['class_id'],
                    'date'             => $validated['date'],
                    'material'         => $validated['material'] ?? null,
                    'number_of_pupils' => count($pupilIds),
                    'photo_file'       => $photoPath,
                ]);

                foreach ($allPupilIds as $pid) {
                    \App\Models\PupilPresence::create([
                        'class_session_id' => $session->id,
                        'pupil_id'         => $pid,
                        'status'           => in_array($pid, $pupilIds) ? 'presence' : 'absent',
                    ]);
                }
            }

            $session->load('schoolClass.courseType');
            $rate   = self::getRate($session, $tutor->id);
            $amount = $this->calcAmount($session, $tutor->id, $validated['status']);

            $presence = TutorPresence::create([
                'class_session_id' => $session->id,
                'tutor_id'         => $tutor->id,
                'status'           => $validated['status'],
                'amount'           => $amount,
                'rate'             => $rate,
                'note'             => $validated['note'] ?? null,
            ]);

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
        $user = Auth::user();
        $tutor = $user->tutor;

        if (! $tutor || $presence->tutor_id !== $tutor->id) abort(403);

        $validated = $request->validate([
            'note'         => 'nullable|string|max:500',
            'material'     => 'nullable|string|max:500',
            'week'         => 'nullable|date',
            'session_date' => 'nullable|date',
            'photo'        => 'nullable|image|max:6144',
            'pupil_ids'    => 'nullable|array',
            'pupil_ids.*'  => 'exists:pupils,id',
        ]);

        $validated['status'] = 'presence';

        $presence->load('classSession.schoolClass.courseType');
        $rate      = self::getRate($presence->classSession, $tutor->id);
        $isPrivate = strtolower($presence->classSession->schoolClass->courseType?->name ?? '') === 'private';

        // Sesi private yang anaknya sudah dihapus: jangan lanjut (insert pupil_presences akan langgar FK)
        if ($isPrivate
            && $presence->classSession->pupil_id
            && ! \App\Models\Pupil::whereKey($presence->classSession->pupil_id)->exists()
        ) {
            return back()->with('error', 'Data murid untuk sesi ini sudah tidak tersedia. Silakan hubungi admin untuk memeriksa data siswa.');
        }

        DB::transaction(function () use ($presence, $validated, $rate, $request, $isPrivate) {
            $sessionUpdate = ['material' => $validated['material'] ?? null];
            if (! empty($validated['session_date'])) {
                $sessionUpdate['date'] = $validated['session_date'];
            }
            if ($request->hasFile('photo')) {
                $oldPhoto = $presence->classSession->photo_file;
                if ($oldPhoto) Storage::disk('public')->delete($oldPhoto);
                $path = $request->file('photo')->store('session-photos', 'public');
                $this->compressPhoto($path);
                $sessionUpdate['photo_file'] = preg_replace('/\.[^.]+$/', '.jpg', $path);
            }

            if ($isPrivate) {
                // Sesi private = satu anak; jaga presensi anak tsb tetap hadir
                if ($presence->classSession->pupil_id) {
                    \App\Models\PupilPresence::updateOrCreate(
                        ['class_session_id' => $presence->classSession->id, 'pupil_id' => $presence->classSession->pupil_id],
                        ['status' => 'presence']
                    );
                    $sessionUpdate['number_of_pupils'] = 1;
                }
            } else {
                // Regular: presensi per anak dari pilihan
                $pupilIds = $validated['pupil_ids'] ?? null;
                $this->syncPupilPresences($presence->classSession, $pupilIds);
                $sessionUpdate['number_of_pupils'] = $presence->classSession->pupilPresences()->where('status', 'presence')->count();
            }

            $presence->classSession->update($sessionUpdate);

            $amount = $this->calcAmount($presence->classSession->fresh(), $presence->tutor_id, 'presence');
            $presence->update([
                'status' => 'presence',
                'amount' => $amount,
                'rate'   => $rate,
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
    public static function getPivot(ClassSession $session, int $tutorId): ?object
    {
        return Tutor::find($tutorId)?->classes()
            ->where('classes.id', $session->class_id)
            ->first()?->pivot;
    }

    public static function getRate(ClassSession $session, int $tutorId): float
    {
        $pivot          = self::getPivot($session, $tutorId);
        $courseTypeName = $session->schoolClass->courseType?->name ?? '';

        // Use pivot amount if explicitly set, otherwise fall back to config default
        $isPrivate = strtolower($courseTypeName) === 'private';
        $base = (float) ($pivot?->amount ?? 0);
        if ($base === 0.0) {
            $base = $isPrivate
                ? (float) config('presence.tutor_rate_private')
                : (float) config('presence.tutor_rate_regular');
        }

        // Private: extra per-anak (tutor_pupil). Regular: extra per-kelas (tutor_classes).
        if ($isPrivate && $session->pupil_id) {
            $extra = (float) (DB::table('tutor_pupil')
                ->where('tutor_id', $tutorId)
                ->where('pupil_id', $session->pupil_id)
                ->value('extra_fee') ?? 0);
        } else {
            $extra = (float) ($pivot?->extra_fee ?? 0);
        }

        return $base + $extra;
    }

    public static function calcAmount(ClassSession $session, int $tutorId, string $status): float
    {
        if ($status !== 'presence') return 0;

        $rate           = self::getRate($session, $tutorId);
        $courseTypeName = $session->schoolClass->courseType?->name ?? '';

        if (strtolower($courseTypeName) === 'regular') {
            $pupilsHadir = $session->pupilPresences()
                ->where('status', 'presence')
                ->count();

            $pivot = self::getPivot($session, $tutorId);
            $extra = (float) ($pivot?->extra_fee ?? 0);

            $minPupils = (int) config('presence.regular_min_pupils');

            if ($pupilsHadir < $minPupils) {
                return (float) config('presence.regular_min_incentive') + $extra;
            }

            $base = $rate - $extra;

            return ($base * $pupilsHadir) + $extra;
        }

        return $rate;
    }

    public function destroy(TutorPresence $presence)
    {
        $this->deletePresenceAndSession($presence);

        return back()->with('success', 'Presensi berhasil dihapus.');
    }

    public function destroyMyPresence(TutorPresence $presence)
    {
        $tutor = Auth::user()->tutor;

        if (! $tutor || $presence->tutor_id !== $tutor->id) abort(403);

        $week = $presence->classSession?->date
            ? \Carbon\Carbon::parse($presence->classSession->date)->startOfWeek(\Carbon\Carbon::MONDAY)->format('Y-m-d')
            : now()->format('Y-m-d');

        $this->deletePresenceAndSession($presence);

        return redirect()->route('my-presences', ['date' => $week])
            ->with('success', 'Presensi berhasil dihapus.');
    }

    private function deletePresenceAndSession(TutorPresence $presence): void
    {
        $presence->load('classSession');
        $session = $presence->classSession;

        DB::transaction(function () use ($presence, $session) {
            // Hapus salary record
            TutorSalary::where('tutor_presence_id', $presence->id)->delete();

            // Hapus tutor presence
            $presence->delete();

            // Jika session tidak lagi punya tutor presence lain, hapus session beserta data terkait
            if ($session && $session->tutorPresences()->count() === 0) {
                // Hapus foto
                if ($session->photo_file) {
                    Storage::disk('public')->delete($session->photo_file);
                }
                // Hapus pupil presences
                $session->pupilPresences()->delete();
                // Hapus session
                $session->delete();
            }
        });
    }

    private function syncPupilPresences(ClassSession $session, ?array $presentIds): void
    {
        $allPupilIds = \App\Models\Pupil::whereHas('classes', fn($q) => $q->where('classes.id', $session->class_id))
            ->where('active_status', true)
            ->pluck('id');

        foreach ($allPupilIds as $pid) {
            $status = ($presentIds !== null && in_array($pid, $presentIds)) ? 'presence' : 'absent';
            \App\Models\PupilPresence::updateOrCreate(
                ['class_session_id' => $session->id, 'pupil_id' => $pid],
                ['status' => $status]
            );
        }
    }

    private function compressPhoto(string $storagePath, int $maxDim = 1920, int $quality = 82): void
    {
        $fullPath = Storage::disk('public')->path($storagePath);
        $newPath  = preg_replace('/\.[^.]+$/', '.jpg', $storagePath);

        (new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Imagick\Driver()))
            ->decode($fullPath)
            ->scaleDown($maxDim, $maxDim)
            ->encode(new \Intervention\Image\Encoders\JpegEncoder($quality))
            ->save(Storage::disk('public')->path($newPath));

        if ($newPath !== $storagePath) {
            Storage::disk('public')->delete($storagePath);
        }
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
