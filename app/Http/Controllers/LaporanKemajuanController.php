<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class LaporanKemajuanController extends Controller
{
    public function index(Request $request)
{
    $search = $request->input('search');
    $user = Auth::user();

    // 1. Query Dasar untuk Tabel
    $query = Proposal::with(['user', 'reviewers']);

    // 2. Filter Role (Security Filter)
    if ($user->role === 'pengaju') {
        $query->where('user_id', $user->id);
    } elseif ($user->role === 'reviewer') {
        $query->whereHas('reviewers', function ($q) use ($user) {
            $q->where('users.id', $user->id);
        });
    }

    // 3. Search Logic
    if ($search) {
        $query->where(function ($q) use ($search) {
            $q->where('judul', 'like', "%{$search}%")
              ->orWhereHas('user', function ($u) use ($search) {
                  $u->where('name', 'like', "%{$search}%");
              });
        });
    }

    $proposals = $query->latest()->paginate(10);

    // 4. Data untuk Dropdown Pilih Proposal (Khusus Pengaju)
    $myProposals = null;
    if ($user->role === 'pengaju') {
        $myProposals = Proposal::where('user_id', $user->id)
            ->where('status',) // Pastikan hanya yang disetujui
            ->latest()
            ->get(['id', 'judul']);
    }

    return view('reviewer.laporan-kemajuan', [
        'proposals' => $proposals,
        'myProposals' => $myProposals,
        'user' => $user, // Mengirim data user login untuk Profile
    ]);
}

    public function store(Request $request)
    {
        $request->validate([
            'proposal_id' => 'required|exists:proposals,id', // ✅ pilih proposal
            'file' => 'required|mimes:pdf,doc,docx|max:10240',
            'keterangan' => 'nullable|string',
        ]);

        if ($request->hasFile('file')) {

            // ✅ ambil proposal yang dipilih + pastikan milik user login
           $proposal = Proposal::where('id', $request->proposal_id)
    ->whereHas('user', function($q) {
        $q->where('username', Auth::user()->username);
    })->first();

            if (!$proposal) {
                return redirect()->back()->with('error', 'Proposal tidak valid / bukan milik Anda.');
            }

            // Hapus file lama jika ada
            if ($proposal->file_laporan && Storage::disk('public')->exists($proposal->file_laporan)) {
                Storage::disk('public')->delete($proposal->file_laporan);
            }

            $path = $request->file('file')->store('laporan_kemajuan', 'public');

            $proposal->update([
                'file_laporan' => $path,
                'keterangan' => $request->keterangan,
            ]);

            return redirect()->back()->with('success', 'Laporan berhasil diunggah!');
        }

        return redirect()->back()->withErrors(['file' => 'Gagal mengunggah file.']);
    }

    /**
     * ✅ DOWNLOAD LAPORAN KEMAJUAN (dipakai oleh route laporan.kemajuan.download)
     */
    public function downloadLaporan($id)
    {
        $proposal = Proposal::findOrFail($id);

        if (!$proposal->file_laporan) {
            return redirect()->back()->with('error', 'File laporan belum tersedia.');
        }

        if (!Storage::disk('public')->exists($proposal->file_laporan)) {
            return redirect()->back()->with('error', 'File laporan tidak ditemukan di storage.');
        }

        return Storage::disk('public')->download($proposal->file_laporan);
    }

}
