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

        // ✅ mulai query dasar
        $query = Proposal::with(['user', 'reviewers']);

        // ✅ FILTER biar riwayat tidak campur
        // - pengaju: hanya proposal miliknya
        // - reviewer: hanya proposal yang ditugaskan ke dia
        // - admin: lihat semua (tidak difilter)
        if (Auth::user()->role === 'pengaju') {
            $query->where('user_id', Auth::id());
        } elseif (Auth::user()->role === 'reviewer') {
            $query->whereHas('reviewers', function ($q) {
                $q->where('users.id', Auth::id());
            });
        }

        // ✅ SEARCH harus digroup, biar orWhereHas gak "nembus" filter di atas
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($u) use ($search) {
                      $u->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $proposals = $query->latest()->paginate(10);

        // ✅ untuk dropdown upload (pengaju saja)
        $myProposals = null;
        if (Auth::user()->role === 'pengaju') {
            $myProposals = Proposal::where('user_id', Auth::id())
                ->latest()
                ->get(['id', 'judul']);
        }

        return view('reviewer.laporan-kemajuan', compact('proposals', 'myProposals'));
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
                ->where('user_id', Auth::id())
                ->first();

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
