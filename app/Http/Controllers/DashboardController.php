<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class DashboardController extends Controller
{
    // Menampilkan halaman dashboard
    public function index()
    {
        $user = Auth::user(); // ambil data user yang sedang login
        return view('dashboard', compact('user'));
    }

    // Update data profil user
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name' => 'required|string|max:255',
            'nidn' => 'nullable|string|max:50',
            'no_telepon' => 'nullable|string|max:20',
            'fakultas' => 'nullable|string|max:100',
            'program_studi' => 'nullable|string|max:100',
            'jabatan' => 'nullable|string|max:100',
        ]);

        /** @var \App\Models\User $user */
        $user->update($request->only([
            'name',
            'nidn',
            'no_telepon',
            'fakultas',
            'program_studi',
            'jabatan',
        ]));

        return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
    }
}
