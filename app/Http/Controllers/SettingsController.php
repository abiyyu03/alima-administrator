<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings.index', ['user' => Auth::user()]);
    }

    public function update(Request $request)
    {
        $request->validate(['theme' => 'required|in:light,dark']);
        $user = Auth::user(); $user->theme = $request->theme; $user->save();
        return back()->with('success', 'Tema berhasil disimpan.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);
        $user = Auth::user(); $user->password = Hash::make($request->password); $user->save();
        return back()->with('success', 'Password berhasil diubah.');
    }
}
