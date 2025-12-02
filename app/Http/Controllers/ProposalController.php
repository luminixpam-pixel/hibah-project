<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proposal;
use Illuminate\Support\Facades\Storage;

class ProposalController extends Controller
{
    /**
     * Halaman daftar proposal (Proposal Dikirim)
     */
    public function index()
    {
        // ambil hanya proposal dengan status "Dikirim"
        $proposals = Proposal::where('status', 'Dikirim')->latest()->get();

        return view('proposal.daftar_proposal', compact('proposals'));
    }

    /**
     * Halaman create (dipanggil route /proposal/create)
     */
    public function create()
    {
        return redirect()->route('proposal.index');
    }

    /**
     * Store proposal dari popup
     */
    public function store(Request $request)
    {
        $request->validate([
            'judul'       => 'required|string|max:255',
            'nama_ketua'  => 'required|string|max:255',
            'biaya'       => 'nullable|string|max:255',
            'anggota'     => 'nullable|array',
            'file'        => 'required|file|mimes:pdf,doc,docx|max:102400',
        ]);

        // Ambil ekstensi asli file
        $extension = $request->file('file')->getClientOriginalExtension();

        // Bersihkan judul biar aman jadi nama file
        $cleanName = preg_replace('/[^A-Za-z0-9\-]/', '', $request->judul);

        // Buat nama file baru
        $finalName = $cleanName . '.' . $extension;

        // Simpan file dengan nama yang sudah dibersihkan
        $filePath = $request->file('file')->storeAs('proposal_files', $finalName, 'public');

        // Konversi anggota[] ke JSON
        $anggotaJson = $request->anggota ? json_encode($request->anggota) : null;

        // Simpan ke database
        Proposal::create([
            'judul'          => $request->judul,
            'nama_ketua'     => $request->nama_ketua,
            'file_path'      => $filePath,
            'anggota'        => $anggotaJson,
            'biaya'          => $request->biaya,
            'status'         => 'Dikirim',      // ← perhatikan kapital D
            'periode'        => null,
            'fakultas_prodi' => null,
            'user_id'        => auth()->id(),
            'pengusul'       => null,
            'reviewer'       => null,
        ]);

        return redirect()->route('proposal.index')->with('success', 'Proposal berhasil diajukan!');
    }

    /**
     * Download file proposal
     */
    public function download($id)
    {
        $proposal = Proposal::findOrFail($id);

        if (!$proposal->file_path) {
            return back()->with('error', 'File belum diupload.');
        }

        $path = storage_path('app/public/' . $proposal->file_path);

        if (!file_exists($path)) {
            return back()->with('error', 'File tidak ditemukan di server.');
        }

        // Download menggunakan nama file yang benar
        return response()->download($path, basename($proposal->file_path));
    }

    /**
     * Pindahkan proposal ke status "Perlu Direview"
     */
    public function moveToPerluDireview(Proposal $proposal)
    {
        // hanya proposal dengan status Dikirim yang boleh dipindah (opsional)
        if ($proposal->status !== 'Dikirim') {
            return back()->with('error', 'Proposal ini tidak dalam status Dikirim.');
        }

        $proposal->status = 'Perlu Direview';
        $proposal->save();

        return back()->with('success', 'Proposal berhasil dipindahkan ke "Perlu Direview".');
    }

    /**
     * Halaman daftar proposal yang Perlu Direview
     */
    public function proposalPerluDireview()
    {
        $proposals = Proposal::where('status', 'Perlu Direview')->latest()->get();

        return view('proposal.proposal-perlu-direview', compact('proposals'));
    }
}
