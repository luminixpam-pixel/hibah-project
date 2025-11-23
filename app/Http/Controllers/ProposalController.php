<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proposal;
use Illuminate\Support\Facades\Storage;

class ProposalController extends Controller
{
    /**
     * Halaman daftar proposal
     */
    public function index()
    {
        $proposals = Proposal::latest()->get();

        // 🔥 WAJIB: gunakan nama file blade yang benar
        return view('proposal.daftar_proposal', compact('proposals'));
    }

    /**
     * Halaman create (dipanggil route /proposal/create)
     * Karena kamu pakai popup, cukup redirect ke daftar saja
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

        // Upload file ke storage/app/public/proposal_files
        $filePath = $request->file('file')->store('proposal_files', 'public');

        // Konversi anggota[] menjadi JSON
        $anggotaJson = $request->anggota ? json_encode($request->anggota) : null;

        // Simpan database sesuai struktur tabel proposals
        Proposal::create([
            'judul'          => $request->judul,
            'nama_ketua'     => $request->nama_ketua,
            'file_path'      => $filePath,
            'anggota'        => $anggotaJson,
            'biaya'          => $request->biaya,

            // Kolom lain (default aman)
            'status'         => 'Dikirim',
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

        return response()->download($path);
    }
}
