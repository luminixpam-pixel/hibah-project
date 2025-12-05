<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Proposal;

class DashboardController extends Controller
{
    // Menampilkan halaman dashboard
    public function index()
    {
        $user = Auth::user(); // ambil data user yang sedang login
        $role = $user->role ?? null;

        // Query dasar, berbeda untuk pengaju vs admin/reviewer
        $baseQuery = Proposal::query();

        if ($role === 'pengaju') {
            $baseQuery->where('user_id', $user->id);
        }

        // Hitung jumlah proposal per status
        $daftarProposalCount    = (clone $baseQuery)->where('status', 'Dikirim')->count();
        $perluDireviewCount     = (clone $baseQuery)->where('status', 'Perlu Direview')->count();
        $sedangDireviewCount    = (clone $baseQuery)->where('status', 'Sedang Direview')->count();
        $reviewSelesaiCount     = (clone $baseQuery)->where('status', 'Review Selesai')->count();
        $disetujuiCount         = (clone $baseQuery)->where('status', 'Disetujui')->count();
        $ditolakCount           = (clone $baseQuery)->where('status', 'Ditolak')->count();
        $direvisiCount          = (clone $baseQuery)->where('status', 'Direvisi')->count();
        $hasilRevisiCount       = (clone $baseQuery)->where('status', 'Hasil Revisi')->count();

        // kirim data ke view dashboard
        return view('dashboard', [
            'user'                 => $user,
            'daftarProposalCount'  => $daftarProposalCount,
            'perluDireviewCount'   => $perluDireviewCount,
            'sedangDireviewCount'  => $sedangDireviewCount,
            'reviewSelesaiCount'   => $reviewSelesaiCount,
            'disetujuiCount'       => $disetujuiCount,
            'ditolakCount'         => $ditolakCount,
            'direvisiCount'        => $direvisiCount,
            'hasilRevisiCount'     => $hasilRevisiCount,
        ]);
    }

    // Update data profil user
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'          => 'required|string|max:255',
            'nidn'          => 'nullable|string|max:50',
            'no_telepon'    => 'nullable|string|max:20',
            'fakultas'      => 'nullable|string|max:100',
            'program_studi' => 'nullable|string|max:100',
            'jabatan'       => 'nullable|string|max:100',
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
