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
    /**
     * Menampilkan Dashboard Utama berdasarkan Role
     */
    public function index(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();
        $role = $user->role ?? null;
        $tahun = $request->get('tahun', date('Y'));

        // ================= BASE QUERY BERDASARKAN ROLE =================
        $baseQuery = Proposal::whereYear('created_at', $tahun);

        if ($role === 'pengaju') {
            $baseQuery->where('user_id', $user->id);
        } elseif ($role === 'reviewer') {
            // Untuk Statistik Card Reviewer: Hitung proposal yang ditugaskan ke dia
            $assignedIds = DB::table('proposal_reviewers')
                ->where('reviewer_id', $user->id)
                ->pluck('proposal_id');
            $baseQuery->whereIn('id', $assignedIds->isEmpty() ? [0] : $assignedIds);
        }

        // ================= HITUNG STATISTIK (Untuk Card) =================
        $stats = [
            'daftarProposalCount'  => (clone $baseQuery)->where('status', 'Dikirim')->count(),
            'perluDireviewCount'   => (clone $baseQuery)->where('status', 'Perlu Direview')->count(),
            'sedangDireviewCount'  => (clone $baseQuery)->where('status', 'Sedang Direview')->count(),
            'reviewSelesaiCount'   => (clone $baseQuery)->whereIn('status', ['Review Selesai', 'Hasil Revisi', 'Disetujui'])->count(),
            'disetujuiCount'       => (clone $baseQuery)->where('status_pendanaan', 'Disetujui')->count(),
            'ditolakCount'         => (clone $baseQuery)->where('status_pendanaan', 'Ditolak')->count(),
            'direvisiCount'        => (clone $baseQuery)->where('status_pendanaan', 'Direvisi')->count(),
            'hasilRevisiCount'     => (clone $baseQuery)->where('status', 'Hasil Revisi')->count(),
        ];

        // ================= DATA KHUSUS REVIEWER (Monitoring Tugas Penilaian) =================
        $tugasReview = null;
        if ($role === 'reviewer') {
            $tugasReview = Proposal::whereIn('id', function($q) use ($user) {
                $q->select('proposal_id')->from('proposal_reviewers')->where('reviewer_id', $user->id);
            })->with('user')->latest()->get();
        }

        // Proposal milik sendiri (untuk Stepper Pengaju/Reviewer)
        $latestProposal = Proposal::where('user_id', $user->id)->latest()->first();

        // Data Tambahan (Admin & Umum)
        $rekapFakultas = ($role === 'admin') ? $this->getRekapFakultas($tahun) : null;
        $ringkasanLaporan = ($role === 'admin') ? $this->getRingkasanAdmin($tahun) : null;
        $dokumenResmi = DokumenResmi::latest()->take(3)->get();
        $notifications = Notification::where('user_id', $user->id)->latest()->take(5)->get();

        return view('dashboard', array_merge($stats, compact(
            'user', 'role', 'tahun', 'latestProposal', 'tugasReview',
            'rekapFakultas', 'ringkasanLaporan', 'dokumenResmi', 'notifications'
        )));
    }

    /**
     * Memperbarui Profil User
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

    /**
     * Menampilkan Riwayat Dosen (Khusus Admin)
     * Menjawab error "Method riwayatDosen does not exist"
     */
    public function riwayatDosen(Request $request)
    {
        $search = $request->get('search');

        $riwayatDosen = User::where('role', 'pengaju')
            ->when($search, function ($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nidn', 'like', "%{$search}%");
                });
            })
            ->withCount([
                'proposals as total_pengajuan',
                'proposals as total_disetujui' => function ($query) {
                    $query->where('status_pendanaan', 'Disetujui');
                }
            ])
            ->withSum(['proposals as total_dana' => function ($query) {
                $query->where('status_pendanaan', 'Disetujui');
            }], 'biaya')
            ->get();

        return view('admin.riwayat_dosen', compact('riwayatDosen'));
    }

    // ================= HELPER METHODS (Private) =================

    private function getRekapFakultas($tahun) {
        return Proposal::join('users', 'proposals.user_id', '=', 'users.id')
            ->whereYear('proposals.created_at', $tahun)
            ->select('users.fakultas',
                DB::raw('count(proposals.id) as total_pengajuan'),
                DB::raw('sum(case when proposals.status_pendanaan = "Disetujui" then 1 else 0 end) as total_disetujui'),
                DB::raw('sum(proposals.biaya) as total_biaya'))
            ->groupBy('users.fakultas')->get();
    }

    private function getRingkasanAdmin($tahun) {
        return [
            'total_dana' => Proposal::whereYear('created_at', $tahun)->where('status_pendanaan', 'Disetujui')->sum('biaya'),
            'total_penerima' => Proposal::whereYear('created_at', $tahun)->where('status_pendanaan', 'Disetujui')->count(),
            'total_pengajuan' => Proposal::whereYear('created_at', $tahun)->count(),
        ];
    }
}
