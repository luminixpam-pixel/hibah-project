<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\User; // <-- tambah: untuk ambil data reviewer
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
     * Halaman create (redirect ke index)
     */
    public function create()
    {
        return redirect()->route('proposal.index');
    }

    /**
     * Store proposal dari form
     */
    public function store(Request $request)
{
    $request->validate([
        'judul'      => 'required|string|max:255',
        'nama_ketua' => 'required|string|max:255',
        'biaya'      => 'nullable|string|max:255',
        'anggota'    => 'nullable|array',
        'file'       => 'required|file|mimes:pdf,doc,docx|max:102400',
    ]);

    try {
        // Ambil ekstensi asli file
        $extension = $request->file('file')->getClientOriginalExtension();
        $cleanName = preg_replace('/[^A-Za-z0-9\-]/', '', $request->judul);
        $finalName = $cleanName . '.' . $extension;

        // Simpan file
        $filePath = $request->file('file')->storeAs('proposal_files', $finalName, 'public');

        // Simpan data proposal
        $proposal = Proposal::create([
            'judul'          => $request->judul,
            'nama_ketua'     => $request->nama_ketua,
            'file_path'      => $filePath,
            'anggota'        => $request->anggota ? json_encode($request->anggota) : null,
            'biaya'          => $request->biaya,
            'status'         => 'Dikirim',
            'periode'        => null,
            'fakultas_prodi' => null,
            'user_id'        => auth()->id(),
            'pengusul'       => null,
            'reviewer'       => null,
        ]);

        // Notifikasi otomatis ke user login
        auth()->user()->notifications()->create([
            'title' => 'Proposal Anda berhasil dikirim',
            'type' => 'success',
            'is_read' => false,
        ]);

        return redirect()->route('proposal.index')->with('success', 'Proposal berhasil diajukan!');

    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Gagal upload proposal: ' . $e->getMessage());
    }
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
        // semua proposal dengan status "Perlu Direview"
        $proposals = Proposal::where('status', 'Perlu Direview')->latest()->get();

        // semua user yang berperan sebagai reviewer
        $reviewers = User::where('role', 'reviewer')->orderBy('name')->get();

        return view('proposal.proposal-perlu-direview', compact('proposals', 'reviewers'));
    }

    /**
     * Set / ganti reviewer untuk 1 proposal
     */
    public function assignReviewer(Request $request, Proposal $proposal)
    {
        $request->validate([
            'reviewer' => 'nullable|string|max:255',
        ]);

        // kalau dropdown dikosongkan, reviewer = null
        $proposal->reviewer = $request->reviewer ?: null;
        $proposal->save();

        return back()->with('success', 'Reviewer berhasil diperbarui!');
    }
}
