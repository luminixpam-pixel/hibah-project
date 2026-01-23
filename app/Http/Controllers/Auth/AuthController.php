<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLoginForm() {
        return view('auth.login');
    }

    public function login(Request $request) {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        // 1. Cek Login Lokal (Admin / User yang sudah di-override)
        $userLokal = User::where('username', $request->username)->first();
        if ($userLokal && $userLokal->password && Hash::check($request->password, $userLokal->password)) {
            Auth::login($userLokal);
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        // 2. Cek Login LDAP
        try {
            if (Auth::attempt(['uid' => $request->username, 'password' => $request->password])) {
                $ldapUser = Auth::user();

                // Sinkronisasi data LDAP ke Database Lokal
                $user = User::updateOrCreate(
                    ['username' => $request->username],
                    [
                        'name'          => $ldapUser->displayname[0] ?? $request->username,
                        'email'         => $ldapUser->mail[0] ?? null,
                        'nidn'          => $ldapUser->description[0] ?? null,
                        'fakultas'      => $ldapUser->department[0] ?? null,
                        'program_studi' => $ldapUser->title[0] ?? null,
                        'jabatan'       => $ldapUser->physicaldeliveryofficename[0] ?? null,
                        'guid'          => $ldapUser->getConvertedGuid(),
                        'domain'        => 'default',
                        'role'          => $userLokal ? $userLokal->role : 'pengaju',
                    ]
                );

                Auth::login($user);
                $request->session()->regenerate();
                return redirect()->intended('/dashboard');
            }
        } catch (\Exception $e) {
            // Log::error($e->getMessage());
        }

        return back()->withErrors(['username' => 'Kredensial salah atau server LDAP tidak merespon.']);
    }
public function adminUpdate(Request $request, $id)
{
    $user = User::findOrFail($id);
    $currentUser = auth()->user();

    // 1. Validasi: Admin bisa edit semua orang, User hanya bisa edit dirinya sendiri
    if ($currentUser->role !== 'admin' && $currentUser->id !== $user->id) {
        return back()->with('error', 'Akses ditolak.');
    }

    if ($currentUser->role === 'admin') {
        // --- LOGIKA ADMIN ---
        $request->validate([
            'email'    => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
        ]);

        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = \Illuminate\Support\Facades\Hash::make($request->password);
        }
    } else {
        // --- LOGIKA USER (Pengaju/Reviewer) ---
        $request->validate([
            'email'    => 'required|email|unique:users,email,' . $id,
            'nidn'     => 'required|string|max:20',
            'fakultas' => 'required|string',
        ]);

        $user->email = $request->email;
        $user->nidn = $request->nidn;
        $user->fakultas = $request->fakultas;
    }

    $user->save();
    return back()->with('success', 'Profil berhasil diperbarui!');
}

public function update(Request $request, $id)
{
    $user = User::findOrFail($id);

    // Validasi input
    $request->validate([
        'email' => 'required|email|unique:users,email,' . $user->id,
        'nidn' => 'nullable|string',
        'fakultas' => 'nullable|string',
    ]);

    // Update data
    $user->update([
        'email' => $request->email,
        'nidn' => $request->nidn,
        'fakultas' => $request->fakultas,
    ]);

    return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
}
  public function logout(Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

}
