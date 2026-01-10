<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Fakultas;

class ProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        return view('profile.show', compact('user'));
    }

    public function edit()
    {
        $user = Auth::user();
        $list_fakultas = Fakultas::all();
        return view('profile.edit', compact('user', 'list_fakultas'));
    }

    public function update(Request $request)
{
    $user = Auth::user();

    $request->validate([
        'name'  => 'required|string|max:255',
        'email' => 'required|email|unique:users,email,' . $user->id,
        'password' => 'nullable|min:6'
    ]);

    $user->name = $request->name;
    $user->email = $request->email;
    $user->nidn = $request->nidn;
    $user->phone = $request->phone;
    $user->fakultas = $request->fakultas;
    $user->prodi = $request->prodi;
    $user->jabatan = $request->jabatan;

    if ($request->filled('password')) {
        $user->password = Hash::make($request->password);
    }

    /** @var \App\Models\User $user */
    $user->save();

    return redirect()->route('profile.show')->with('success', 'Profil berhasil diperbarui!');
}

}
