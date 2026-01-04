<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Proposal;
use App\Models\Notification;
use App\Models\DokumenResmi;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $role = $user->role ?? null;

        // Filter Tahun (Default: Tahun Sekarang)
        $tahun = $request->get('tahun', date('Y'));

        // ================= PROPOSAL (BASE QUERY) =================
        $baseQuery = Proposal::query();

        // Jika user adalah pengaju, hanya bisa melihat data miliknya sendiri
        if ($role === 'pengaju') {
            $baseQuery->where('user_id', $user->id);
        }

        // ✅ TAMBAHAN: Jika user adalah reviewer, hanya melihat proposal yang ditugaskan ke dia
        if ($role === 'reviewer') {
            $proposalIds = DB::table('proposal_reviewers')
                ->where('reviewer_id', $user->id) // <-- kalau kolomnya 'user_id', ganti jadi ->where('user_id', $user->id)
                ->pluck('proposal_id');

            // Jika belum ada tugas, paksa hasil kosong (biar tidak error dan tidak tampil semua proposal)
            $baseQuery->whereIn('id', $proposalIds->isEmpty() ? [0] : $proposalIds->toArray());
        }

        // ================= STATISTIK COUNTER (Sesuai Route monitoring.*) =================

        // 1. Daftar Proposal / Dikirim (route: monitoring.proposalDikirim)
        $daftarProposalCount  = (clone $baseQuery)->whereYear('created_at', $tahun)->where('status', 'Dikirim')->count();

        // 2. Perlu Direview (route: monitoring.proposalPerluDireview)
        $perluDireviewCount   = (clone $baseQuery)->whereYear('created_at', $tahun)->where('status', 'Perlu Direview')->count();

        // 3. Sedang Direview (route: monitoring.proposalSedangDireview)
        $sedangDireviewCount  = (clone $baseQuery)->whereYear('created_at', $tahun)->where('status', 'Sedang Direview')->count();

        // 4. Disetujui (route: monitoring.proposalDisetujui)
        $disetujuiCount       = (clone $baseQuery)->whereYear('created_at', $tahun)->where('status', 'Disetujui')->count();

        // 5. Ditolak (route: monitoring.proposalDitolak)
        $ditolakCount         = (clone $baseQuery)->whereYear('created_at', $tahun)->where('status', 'Ditolak')->count();

        // 6. Direvisi (route: monitoring.proposalDirevisi)
        $direvisiCount        = (clone $baseQuery)->whereYear('created_at', $tahun)->where('status', 'Direvisi')->count();

        // 7. Hasil Revisi (route: monitoring.hasilRevisi)
        $hasilRevisiCount     = (clone $baseQuery)->whereYear('created_at', $tahun)->where('status', 'Hasil Revisi')->count();

        // 8. Review Selesai (route: monitoring.reviewSelesai)
        $reviewSelesaiCount   = (clone $baseQuery)->whereYear('created_at', $tahun)
                                ->whereIn('status', ['Review Selesai', 'Hasil Revisi', 'Disetujui'])
                                ->count();

        // ================= ANALITIK FAKULTAS (KHUSUS ADMIN) =================
        $rekapFakultas = null;
        if ($role === 'admin') {
            $rekapFakultas = Proposal::join('users', 'proposals.user_id', '=', 'users.id')
                ->whereYear('proposals.created_at', $tahun)
                ->select(
                    'users.fakultas',
                    DB::raw('count(proposals.id) as total_aju'),
                    DB::raw('sum(case when proposals.status = "Disetujui" then 1 else 0 end) as total_setuju'),
                    DB::raw('sum(proposals.biaya) as total_biaya')
                )
                ->groupBy('users.fakultas')
                ->get();
        }

        // ================= DOKUMEN & NOTIFIKASI =================
        $dokumenResmi = DokumenResmi::latest()->get();
        $notifications = Notification::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->take(5)
                        ->get();

        return view('dashboard', compact(
            'user', 'role', 'tahun', 'dokumenResmi', 'notifications',
            'daftarProposalCount', 'perluDireviewCount', 'sedangDireviewCount',
            'reviewSelesaiCount', 'disetujuiCount', 'ditolakCount',
            'direvisiCount', 'hasilRevisiCount', 'rekapFakultas'
        ));
    }

    /**
     * Sesuai Route: Route::post('/dashboard/update', ...)
     */
    public function updateProfile(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $request->validate([
            'name'          => 'required|string|max:255',
            'nidn'          => 'nullable|string|max:50',
            'no_telepon'    => 'nullable|string|max:20',
            'fakultas'      => 'nullable|string',
            'program_studi' => 'nullable|string|max:100',
            'jabatan'       => 'nullable|string|max:100',
        ]);

        $user->update($request->only([
            'name', 'nidn', 'no_telepon', 'fakultas', 'program_studi', 'jabatan',
        ]));

        return redirect()->back()->with('success', 'Profil berhasil diperbarui!');
    }
}
