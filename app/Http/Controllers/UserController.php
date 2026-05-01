<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Tutor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->search;
        $roleId = $request->role_id;

        $users = User::with(['role', 'tutor'])
            ->when($search, fn($q) => $q->where(fn($q2) =>
                $q2->where('name', 'like', "%{$search}%")
                   ->orWhere('email', 'like', "%{$search}%")
            ))
            ->when($roleId, fn($q) => $q->where('role_id', $roleId))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $roles = Role::orderBy('name')->get();

        return view('users.index', compact('users', 'roles', 'search', 'roleId'));
    }

    public function create()
    {
        $roles  = Role::orderBy('name')->get();
        $tutors = Tutor::whereDoesntHave('user')->orderBy('name')->get();

        return view('users.create', compact('roles', 'tutors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
            'role_id'  => 'required|exists:roles,id',
            'tutor_id' => 'nullable|exists:tutors,id|unique:users,tutor_id',
        ]);

        User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id'  => $validated['role_id'],
            'tutor_id' => $validated['tutor_id'] ?? null,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles  = Role::orderBy('name')->get();
        $tutors = Tutor::whereDoesntHave('user')
            ->orWhere('id', $user->tutor_id)
            ->orderBy('name')
            ->get();

        return view('users.edit', compact('user', 'roles', 'tutors'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => ['nullable', 'confirmed', Password::min(8)],
            'role_id'  => 'required|exists:roles,id',
            'tutor_id' => 'nullable|exists:tutors,id|unique:users,tutor_id,' . $user->id,
        ]);

        $data = [
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'role_id'  => $validated['role_id'],
            'tutor_id' => $validated['tutor_id'] ?? null,
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return back()->with('success', 'User berhasil dihapus.');
    }
}
