<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Fakultas;
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

        // --- TAMBAHKAN INI ---
        // Diperlukan agar form popup pengaju tidak kosong/error
        $list_fakultas = Fakultas::all();

        $all_dosen = User::where('role', 'pengaju')->get();

        // ================= BASE QUERY BERDASARKAN ROLE =================
        $baseQuery = Proposal::whereYear('created_at', $tahun);

        if ($role === 'pengaju') {
            $baseQuery->where('user_id', $user->id);
        } elseif ($role === 'reviewer') {
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

        // ================= DATA KHUSUS REVIEWER =================
        $tugasReview = null;
        if ($role === 'reviewer') {
            $tugasReview = Proposal::whereIn('id', function($q) use ($user) {
                $q->select('proposal_id')->from('proposal_reviewers')->where('reviewer_id', $user->id);
            })->with('user')->latest()->get();
        }

        $latestProposal = Proposal::where('user_id', $user->id)->latest()->first();

        // ================= DATA KHUSUS ADMIN =================
        // Berikan default collect() agar @forelse di Blade tidak error jika bukan admin
        $rekapFakultas = ($role === 'admin') ? $this->getRekapFakultas($tahun) : collect();
        $ringkasanLaporan = ($role === 'admin') ? $this->getRingkasanAdmin($tahun) : null;

        $dokumenResmi = DokumenResmi::latest()->take(3)->get();
        $notifications = Notification::where('user_id', $user->id)->latest()->take(5)->get();

        // Tambahkan 'list_fakultas' ke dalam compact
        return view('dashboard', array_merge($stats, compact(
            'user', 'role', 'tahun', 'latestProposal', 'tugasReview', 'list_fakultas',
            'rekapFakultas', 'ringkasanLaporan', 'dokumenResmi', 'notifications','all_dosen'
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

    /**
     * DETAIL RIWAYAT DOSEN (Khusus Admin)
     * Route: /riwayat-dosen/{id}
     */
    public function detailDosen($id)
    {
        $dosen = User::where('role', 'pengaju')->findOrFail($id);

        $proposals = Proposal::with(['fakultas'])
            ->where('user_id', $dosen->id)
            ->latest()
            ->get();

        $totalPengajuan = $proposals->count();
        $totalDisetujui = $proposals->where('status_pendanaan', 'Disetujui')->count();
        $totalDitolak   = $proposals->where('status_pendanaan', 'Ditolak')->count();
        $totalDirevisi  = $proposals->where('status_pendanaan', 'Direvisi')->count();
        $totalDana      = $proposals->where('status_pendanaan', 'Disetujui')->sum('biaya');
        $successRate    = $totalPengajuan > 0 ? ($totalDisetujui / $totalPengajuan) * 100 : 0;

        $stats = compact(
            'totalPengajuan',
            'totalDisetujui',
            'totalDitolak',
            'totalDirevisi',
            'totalDana',
            'successRate'
        );

        return view('admin.detail_riwayat_dosen', compact('dosen', 'proposals', 'stats'));
    }

    // ================= HELPER METHODS (Private) =================

    private function getRekapFakultas($tahun) {
        return DB::table('fakultas')
            // Kita hubungkan tabel fakultas dengan tabel proposals
            ->leftJoin('proposals', 'fakultas.id', '=', 'proposals.fakultas_prodi')
            ->select(
                'fakultas.nama_fakultas as fakultas', // INI PENTING: Mengambil teks nama
                DB::raw('COUNT(proposals.id) as total_pengajuan'),
                DB::raw("SUM(CASE WHEN proposals.status_pendanaan = 'Disetujui' THEN 1 ELSE 0 END) as total_disetujui"),
                DB::raw('SUM(IFNULL(proposals.biaya, 0)) as total_biaya')
            )
            // Filter berdasarkan tahun proposal
            ->whereYear('proposals.created_at', $tahun)
            ->groupBy('fakultas.id', 'fakultas.nama_fakultas')
            ->get();
    }

    private function getRingkasanAdmin($tahun) {
        return [
            'total_dana' => Proposal::whereYear('created_at', $tahun)->where('status_pendanaan', 'Disetujui')->sum('biaya'),
            'total_penerima' => Proposal::whereYear('created_at', $tahun)->where('status_pendanaan', 'Disetujui')->count(),
            'total_pengajuan' => Proposal::whereYear('created_at', $tahun)->count(),
        ];
    }

    public function updateRole(Request $request, $id)
    {
        // Pastikan hanya admin yang bisa akses
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $user = User::findOrFail($id);
        $user->role = $request->role; // 'admin', 'reviewer', atau 'pengaju'
        $user->save();

        return back()->with('success', 'Role user ' . $user->name . ' berhasil diubah!');
    }
}
