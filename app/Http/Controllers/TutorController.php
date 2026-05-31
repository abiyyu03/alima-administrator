<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\SchoolClass;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TutorController extends Controller
{
    public function index()
    {
        $tutors = Tutor::with(['classes.grade', 'classes.courseType', 'user'])->orderBy('name')->paginate(15);

        return view('tutors.index', compact('tutors'));
    }

    public function create()
    {
        $classes = SchoolClass::with(['grade', 'courseType', 'pupils'])->orderBy('name')->get();
        $assignedPupilIds    = collect();
        $assignedPupilExtras = collect();

        return view('tutors.create', compact('classes', 'assignedPupilIds', 'assignedPupilExtras'));
    }

    public function store(Request $request)
    {
        $this->normalizeCurrencyInputs($request);

        $request->validate([
            'name'           => 'required|string|max:150',
            'telp'           => 'nullable|string|max:20',
            'dob'            => 'nullable|date',
            'domicille'      => 'nullable|string|max:200',
            'email'          => 'required|email|unique:users,email',
            'password'       => 'required|string|min:8|confirmed',
            'class_ids'      => 'nullable|array',
            'class_ids.*'    => 'exists:classes,id',
            'amounts'        => 'nullable|array',
            'amounts.*'      => 'nullable|numeric|min:0',
            'extra_fees'     => 'nullable|array',
            'extra_fees.*'   => 'nullable|integer|min:0',
            'pupil_ids'      => 'nullable|array',
            'pupil_ids.*'    => 'exists:pupils,id',
            'pupil_extra'    => 'nullable|array',
            'pupil_extra.*'  => 'nullable|integer|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $tutor = Tutor::create($request->only('name', 'telp', 'dob', 'domicille'));

            $tutorRole = Role::where('name', 'tutor')->first();

            User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role_id'  => $tutorRole?->id,
                'tutor_id' => $tutor->id,
            ]);

            $this->syncClassesWithAmounts($tutor, $request);
            $this->syncTutorPupils($tutor, $request);
        });

        return redirect()->route('tutors.index')->with('success', 'Tutor dan akun login berhasil dibuat.');
    }

    public function edit(Tutor $tutor)
    {
        $tutor->load('classes.grade', 'classes.courseType', 'pupils');
        $classes = SchoolClass::with(['grade', 'courseType', 'pupils'])->orderBy('name')->get();

        $assignedAmounts     = $tutor->classes->pluck('pivot.amount', 'id');
        $assignedExtraFees   = $tutor->classes->pluck('pivot.extra_fee', 'id');
        $assignedPupilIds    = $tutor->pupils->pluck('id');
        $assignedPupilExtras = $tutor->pupils->pluck('pivot.extra_fee', 'id');

        return view('tutors.edit', compact('tutor', 'classes', 'assignedAmounts', 'assignedExtraFees', 'assignedPupilIds', 'assignedPupilExtras'));
    }

    public function update(Request $request, Tutor $tutor)
    {
        $this->normalizeCurrencyInputs($request);

        $request->validate([
            'name'           => 'required|string|max:150',
            'telp'           => 'nullable|string|max:20',
            'dob'            => 'nullable|date',
            'domicille'      => 'nullable|string|max:200',
            'class_ids'      => 'nullable|array',
            'class_ids.*'    => 'exists:classes,id',
            'amounts'        => 'nullable|array',
            'amounts.*'      => 'nullable|numeric|min:0',
            'extra_fees'     => 'nullable|array',
            'extra_fees.*'   => 'nullable|integer|min:0',
            'pupil_ids'      => 'nullable|array',
            'pupil_ids.*'    => 'exists:pupils,id',
            'pupil_extra'    => 'nullable|array',
            'pupil_extra.*'  => 'nullable|integer|min:0',
        ]);

        $tutor->update($request->only('name', 'telp', 'dob', 'domicille'));

        $this->syncClassesWithAmounts($tutor, $request);
        $this->syncTutorPupils($tutor, $request);

        return redirect()->route('tutors.index')->with('success', 'Data tutor berhasil diperbarui.');
    }

    public function destroy(Tutor $tutor)
    {
        DB::transaction(function () use ($tutor) {
            // Hapus akun user yang terhubung
            User::where('tutor_id', $tutor->id)->delete();
            $tutor->classes()->detach();
            $tutor->delete();
        });

        return back()->with('success', 'Tutor dan akun login berhasil dihapus.');
    }

    // ---------------------------------------------------------------

    /**
     * Rapikan input nominal rupiah (amounts/extra_fees/pupil_extra): buang
     * pemisah ribuan & karakter non-digit, kosong → null. Mencegah error
     * validasi saat user mengetik "10.000".
     */
    private function normalizeCurrencyInputs(Request $request): void
    {
        foreach (['amounts', 'extra_fees', 'pupil_extra'] as $key) {
            if (! is_array($request->input($key))) continue;

            $clean = collect($request->input($key))->map(function ($v) {
                if ($v === null || $v === '') return null;
                return (int) preg_replace('/\D/', '', (string) $v);
            })->all();

            $request->merge([$key => $clean]);
        }
    }

    private function syncClassesWithAmounts(Tutor $tutor, Request $request): void
    {
        $classIds  = $request->input('class_ids', []);
        $amounts   = $request->input('amounts', []);
        $extraFees = $request->input('extra_fees', []);

        $classes = \App\Models\SchoolClass::with('courseType')
            ->whereIn('id', $classIds)
            ->get()
            ->keyBy('id');

        $syncData = collect($classIds)->mapWithKeys(function ($classId) use ($amounts, $extraFees, $classes) {
            $inputAmount = (float) ($amounts[$classId] ?? 0);

            if ($inputAmount === 0.0) {
                $typeName    = strtolower($classes->get($classId)?->courseType?->name ?? '');
                $inputAmount = $typeName === 'private'
                    ? (float) config('presence.tutor_rate_private')
                    : (float) config('presence.tutor_rate_regular');
            }

            return [$classId => [
                'amount'    => $inputAmount,
                'extra_fee' => (int) ($extraFees[$classId] ?? 0),
            ]];
        })->toArray();

        $tutor->classes()->sync($syncData);
    }

    /**
     * Simpan anak-anak private yang dipegang tutor. Hanya anak yang benar-benar
     * anggota salah satu kelas (private) yang dicentang yang disimpan.
     */
    private function syncTutorPupils(Tutor $tutor, Request $request): void
    {
        $classIds = $request->input('class_ids', []);
        $pupilIds = $request->input('pupil_ids', []);
        $extras   = $request->input('pupil_extra', []);

        $validPupilIds = SchoolClass::query()
            ->whereIn('id', $classIds)
            ->with('pupils:id')
            ->get()
            ->pluck('pupils.*.id')
            ->flatten()
            ->intersect($pupilIds);

        // Simpan beserta extra rate per anak
        $syncData = $validPupilIds->mapWithKeys(fn($pid) => [
            $pid => ['extra_fee' => (int) ($extras[$pid] ?? 0)],
        ])->all();

        $tutor->pupils()->sync($syncData);
    }
}
