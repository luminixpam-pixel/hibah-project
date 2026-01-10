<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proposal;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class LaporanAkhirController extends Controller
{
    /**
     * Menampilkan daftar proposal untuk laporan akhir
     */
    public function index()
    {
        $user = Auth::user();
        $query = Proposal::query();

        if ($user->role === 'admin') {
            $proposals = $query->with(['user', 'reviewers'])->latest()->get();
            $myProposals = [];
        } else {
            $proposals = $query->where('user_id', $user->id)
                               ->with(['reviewers'])
                               ->latest()
                               ->get();

            // Mengambil proposal milik user untuk dropdown select
            $myProposals = Proposal::where('user_id', $user->id)->get();
        }

        return view('reviewer.laporan-akhir', compact('proposals', 'myProposals'));
    }

    /**
     * Proses simpan berkas laporan akhir
     */
  public function store(Request $request)
{
    // 1. Validasi dengan pesan error kustom agar kita tahu apa yang salah
    $request->validate([
        'proposal_id' => 'required',
        'file' => 'required|mimes:pdf|max:51200',
    ], [
        'file.mimes' => 'File harus berupa PDF.',
        'file.max' => 'File terlalu besar (Maks 50MB).',
    ]);

    try {
        $proposal = Proposal::find($request->proposal_id);

        if (!$proposal) {
            return back()->with('error', 'ID Proposal tidak ditemukan di database.');
        }

        if ($request->hasFile('file')) {
            $file = $request->file('file');

            // Simpan file
            $path = $file->store('laporan_akhir', 'public');

            // 2. Update dengan pengecekan sukses
            $isSuccess = $proposal->update([
                'file_laporan_akhir' => $path,
                'keterangan_akhir' => $request->keterangan,
            ]);

            if ($isSuccess) {
                return back()->with('success', 'Laporan akhir berhasil diunggah!');
            } else {
                return back()->with('error', 'Database menolak update. Cek $fillable di Model Proposal.');
            }
        }
    } catch (\Exception $e) {
        // Tampilkan pesan error asli jika ada masalah SQL
        return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
    }

    return back()->with('error', 'Gagal: File tidak terbaca oleh server.');
}

    /**
     * Fungsi Download Berkas Laporan Akhir
     */
    public function download($id)
    {
        $proposal = Proposal::findOrFail($id);

        // Cek keberadaan file di kolom file_laporan_akhir
        if (!$proposal->file_laporan_akhir || !Storage::disk('public')->exists($proposal->file_laporan_akhir)) {
            return back()->with('error', 'File tidak ditemukan di server.');
        }

        $extension = pathinfo($proposal->file_laporan_akhir, PATHINFO_EXTENSION);
        $cleanTitle = str_replace([' ', '/', '\\'], '_', $proposal->judul);
        $downloadName = "Laporan_Akhir_" . $cleanTitle . "." . $extension;

        return Storage::disk('public')->download($proposal->file_laporan_akhir, $downloadName);
    }
}
