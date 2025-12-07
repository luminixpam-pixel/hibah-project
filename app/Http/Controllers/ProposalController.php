<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\User;   // untuk ambil data reviewer
use App\Models\Review; // untuk halaman review selesai
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
                'title'   => 'Proposal Anda berhasil dikirim',
                'type'    => 'success',
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
     * HALAMAN TINJAU PROPOSAL (DETAIL SEDERHANA)
     */
    public function tinjau($id)
    {
        $proposal = Proposal::findOrFail($id);

        return view('proposal.tinjau-proposal', compact('proposal'));
    }

    /**
     * HALAMAN EDIT PROPOSAL (untuk pemilik proposal / pengaju)
     */
    public function edit($id)
    {
        $proposal = Proposal::findOrFail($id);

        // hanya pemilik + role pengaju yang boleh edit
        if (auth()->id() !== $proposal->user_id || auth()->user()->role !== 'pengaju') {
            abort(403, 'Anda tidak berhak mengedit proposal ini.');
        }

        return view('proposal.edit-proposal', compact('proposal'));
    }

    /**
     * UPDATE PROPOSAL
     */
    public function update(Request $request, $id)
    {
        $proposal = Proposal::findOrFail($id);

        if (auth()->id() !== $proposal->user_id || auth()->user()->role !== 'pengaju') {
            abort(403, 'Anda tidak berhak mengedit proposal ini.');
        }

        $request->validate([
            'judul'      => 'required|string|max:255',
            'nama_ketua' => 'required|string|max:255',
            'biaya'      => 'nullable|string|max:255',
            'anggota'    => 'nullable|array',
            'file'       => 'nullable|file|mimes:pdf,doc,docx|max:102400',
        ]);

        // update field dasar
        $proposal->judul      = $request->judul;
        $proposal->nama_ketua = $request->nama_ketua;
        $proposal->biaya      = $request->biaya;
        $proposal->anggota    = $request->anggota ? json_encode($request->anggota) : null;

        // jika upload file baru, ganti file lama
        if ($request->hasFile('file')) {
            if ($proposal->file_path && Storage::disk('public')->exists($proposal->file_path)) {
                Storage::disk('public')->delete($proposal->file_path);
            }

            $extension = $request->file('file')->getClientOriginalExtension();
            $cleanName = preg_replace('/[^A-Za-z0-9\-]/', '', $request->judul);
            $finalName = $cleanName . '.' . $extension;

            $filePath = $request->file('file')->storeAs('proposal_files', $finalName, 'public');
            $proposal->file_path = $filePath;
        }

        $proposal->save();

        return redirect()->route('proposal.index')->with('success', 'Proposal berhasil diperbarui.');
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
     * (khusus admin mengatur reviewer)
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
     * Halaman daftar proposal yang Sedang Direview
     * (admin dan reviewer bisa lihat)
     */
    public function proposalSedangDireview()
    {
        $proposals = Proposal::where('status', 'Sedang Direview')->latest()->get();

        return view('proposal.proposal-sedang-direview', compact('proposals'));
    }

    /**
     * Halaman Review Selesai
     * Diambil dari tabel reviews JOIN proposals
     */
    public function reviewSelesai()
    {
        $reviews = Review::select(
                'reviews.*',
                'proposals.judul',
                'proposals.nama_ketua',
                'proposals.reviewer as reviewer_nama',
                'proposals.status as proposal_status'
            )
            ->join('proposals', 'reviews.proposal_id', '=', 'proposals.id')
            ->orderByDesc('reviews.created_at')
            ->get();

        return view('proposal.proposal-selesai', compact('reviews'));
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
