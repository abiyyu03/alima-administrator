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
        $classes = SchoolClass::with(['grade', 'courseType'])->orderBy('name')->get();

        return view('tutors.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'          => 'required|string|max:150',
            'telp'          => 'nullable|string|max:20',
            'dob'           => 'nullable|date',
            'domicille'     => 'nullable|string|max:200',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:8|confirmed',
            'class_ids'     => 'nullable|array',
            'class_ids.*'   => 'exists:classes,id',
            'amounts'       => 'nullable|array',
            'amounts.*'     => 'nullable|numeric|min:0',
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
        });

        return redirect()->route('tutors.index')->with('success', 'Tutor dan akun login berhasil dibuat.');
    }

    public function edit(Tutor $tutor)
    {
        $tutor->load('classes.grade', 'classes.courseType');
        $classes = SchoolClass::with(['grade', 'courseType'])->orderBy('name')->get();

        // Map class_id => amount for pre-filling form
        $assignedAmounts = $tutor->classes->pluck('pivot.amount', 'id');

        return view('tutors.edit', compact('tutor', 'classes', 'assignedAmounts'));
    }

    public function update(Request $request, Tutor $tutor)
    {
        $request->validate([
            'name'          => 'required|string|max:150',
            'telp'          => 'nullable|string|max:20',
            'dob'           => 'nullable|date',
            'domicille'     => 'nullable|string|max:200',
            'class_ids'     => 'nullable|array',
            'class_ids.*'   => 'exists:classes,id',
            'amounts'       => 'nullable|array',
            'amounts.*'     => 'nullable|numeric|min:0',
        ]);

        $tutor->update($request->only('name', 'telp', 'dob', 'domicille'));

        $this->syncClassesWithAmounts($tutor, $request);

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

    private function syncClassesWithAmounts(Tutor $tutor, Request $request): void
    {
        $classIds = $request->input('class_ids', []);
        $amounts  = $request->input('amounts', []);

        // Build sync payload: [class_id => ['amount' => X]]
        $syncData = collect($classIds)->mapWithKeys(function ($classId) use ($amounts) {
            return [$classId => ['amount' => (float) ($amounts[$classId] ?? 0)]];
        })->toArray();

        $tutor->classes()->sync($syncData);
    }
}
