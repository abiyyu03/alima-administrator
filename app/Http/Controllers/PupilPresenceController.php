<?php

namespace App\Http\Controllers;

use App\Models\ClassSession;
use App\Models\Pupil;
use App\Models\PupilPresence;
use App\Models\TutorPresence;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PupilPresenceController extends Controller
{
    public function allSessions(Request $request)
    {
        $search  = $request->search;
        $classId = $request->class_id;

        $pupils = Pupil::with(['schoolClass.grade', 'schoolClass.courseType'])
            ->withCount([
                'presences as total_sesi',
                'presences as total_hadir' => fn($q) => $q->where('status', 'presence'),
            ])
            ->when($classId, fn($q) => $q->where('class_id', $classId))
            ->when($search, fn($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $classes = \App\Models\SchoolClass::with('grade')->orderBy('name')->get();

        return view('pupil-presences.all', compact('pupils', 'classes', 'search', 'classId'));
    }

    public function index(ClassSession $classSession)
    {
        $classSession->load(['schoolClass.grade', 'schoolClass.courseType', 'tutorPresences.tutor']);

        // All pupils enrolled in this class
        $pupils = Pupil::where('class_id', $classSession->class_id)
            ->where('active_status', true)
            ->orderBy('name')
            ->get();

        // Existing presences keyed by pupil_id
        $existing = $classSession->pupilPresences()->get()->keyBy('pupil_id');

        return view('pupil-presences.index', compact('classSession', 'pupils', 'existing'));
    }

    public function store(Request $request, ClassSession $classSession)
    {
        $request->validate([
            'presences'          => 'required|array',
            'presences.*.status' => 'required|in:presence,absent,sick,permission',
            'presences.*.note'   => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($request, $classSession) {
            foreach ($request->input('presences') as $pupilId => $data) {
                PupilPresence::updateOrCreate(
                    ['class_session_id' => $classSession->id, 'pupil_id' => $pupilId],
                    ['status' => $data['status'], 'note' => $data['note'] ?? null]
                );
            }

            $this->recalcTutorAmounts($classSession);
        });

        return back()->with('success', 'Presensi siswa berhasil disimpan.');
    }

    public function update(Request $request, ClassSession $classSession, PupilPresence $pupilPresence)
    {
        $request->validate([
            'status' => 'required|in:presence,absent,sick,permission',
        ]);

        DB::transaction(function () use ($request, $classSession, $pupilPresence) {
            $pupilPresence->update(['status' => $request->status]);
            $this->recalcTutorAmounts($classSession);
        });

        return back()->with('success', 'Status siswa diperbarui.');
    }

    public function destroy(ClassSession $classSession, PupilPresence $pupilPresence)
    {
        DB::transaction(function () use ($classSession, $pupilPresence) {
            $pupilPresence->delete();
            $this->recalcTutorAmounts($classSession);
        });

        return back()->with('success', 'Presensi siswa dihapus.');
    }

    public function pupilDetail(Pupil $pupil)
    {
        $pupil->load('schoolClass.grade');

        $presences = $pupil->presences()
            ->with('classSession.schoolClass.courseType')
            ->orderByDesc(
                \App\Models\ClassSession::select('date')
                    ->whereColumn('id', 'pupil_presences.class_session_id')
            )
            ->paginate(20);

        $allPresences = $pupil->presences()->get();
        $totalSesi  = $allPresences->count();
        $totalHadir = $allPresences->where('status', 'presence')->count();
        $totalAbsen = $totalSesi - $totalHadir;
        $persen     = $totalSesi > 0 ? round($totalHadir / $totalSesi * 100) : 0;

        return view('pupil-presences.pupil', compact('pupil', 'presences', 'totalSesi', 'totalHadir', 'totalAbsen', 'persen'));
    }

    // ---------------------------------------------------------------

    private function recalcTutorAmounts(ClassSession $classSession): void
    {
        $classSession->load('schoolClass.courseType');

        // Only Regular classes have dynamic salary calculation
        if ($classSession->schoolClass->courseType?->name !== 'Regular') return;

        $tutorPresences = TutorPresence::where('class_session_id', $classSession->id)
            ->where('status', 'presence')
            ->get();

        foreach ($tutorPresences as $presence) {
            $amount = TutorPresenceController::calcAmount($classSession, $presence->tutor_id, $presence->status);
            $presence->update(['amount' => $amount]);

            if ($amount > 0) {
                \App\Models\TutorSalary::updateOrCreate(
                    ['tutor_presence_id' => $presence->id],
                    ['tutor_id' => $presence->tutor_id, 'salary' => $amount]
                );
            } else {
                \App\Models\TutorSalary::where('tutor_presence_id', $presence->id)->delete();
            }
        }
    }
}
