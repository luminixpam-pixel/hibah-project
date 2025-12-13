<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\User;
use App\Models\Review;
use Illuminate\Support\Facades\Storage;
use App\Helpers\NotificationHelper;

class ProposalController extends Controller
{
    /**
     * Halaman daftar proposal (Proposal Dikirim)
     */
    public function index()
    {
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
            $extension = $request->file('file')->getClientOriginalExtension();
            $cleanName = preg_replace('/[^A-Za-z0-9\-]/', '', $request->judul);
            $finalName = $cleanName . '.' . $extension;

            $filePath = $request->file('file')->storeAs('proposal_files', $finalName, 'public');

            $proposal = Proposal::create([
                'judul'          => $request->judul,
                'nama_ketua'     => $request->nama_ketua,
                'file_path'      => $filePath,
                'anggota'        => $request->anggota ? json_encode($request->anggota) : null,
                'biaya'          => $request->biaya,
                'status'         => 'Dikirim',
                'user_id'        => auth()->id(),
            ]);

            // 🔔 Notifikasi otomatis ke user sendiri
            NotificationHelper::send(auth()->id(), 'Proposal Anda berhasil dikirim', 'Proposal Anda telah dikirim dan menunggu proses review', 'success');

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
     * HALAMAN TINJAU PROPOSAL
     */
    public function tinjau($id)
    {
        $proposal = Proposal::findOrFail($id);
        return view('proposal.tinjau-proposal', compact('proposal'));
    }

    /**
     * HALAMAN EDIT PROPOSAL
     */
    public function edit($id)
    {
        $proposal = Proposal::findOrFail($id);

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

        $proposal->judul      = $request->judul;
        $proposal->nama_ketua = $request->nama_ketua;
        $proposal->biaya      = $request->biaya;
        $proposal->anggota    = $request->anggota ? json_encode($request->anggota) : null;

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
        if ($proposal->status !== 'Dikirim') {
            return back()->with('error', 'Proposal ini tidak dalam status Dikirim.');
        }

        $proposal->status = 'Perlu Direview';
        $proposal->save();

        // 🔔 Notifikasi ke pengaju
        NotificationHelper::send($proposal->user_id, 'Proposal Anda sedang direview', 'Proposal Anda kini masuk antrian review', 'info');

        return back()->with('success', 'Proposal berhasil dipindahkan ke "Perlu Direview".');
    }

    /**
     * Halaman daftar proposal Perlu Direview (admin)
     */
    public function proposalPerluDireview()
    {
        $proposals = Proposal::where('status', 'Perlu Direview')->latest()->get();
        $reviewers = User::where('role', 'reviewer')->orderBy('name')->get();
        return view('proposal.proposal-perlu-direview', compact('proposals', 'reviewers'));
    }

    /**
     * Halaman daftar proposal Sedang Direview
     */
    public function proposalSedangDireview()
    {
        $proposals = Proposal::where('status', 'Sedang Direview')->latest()->get();
        return view('proposal.proposal-sedang-direview', compact('proposals'));
    }

    /**
     * Halaman Review Selesai
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

public function assignReviewer(Request $request, Proposal $proposal)
{
    $request->validate([
        'reviewer_1' => 'nullable|exists:users,id',
        'reviewer_2' => 'nullable|exists:users,id',
    ]);

    $reviewers = collect([
        $request->reviewer_1,
        $request->reviewer_2,
    ])->filter()->unique()->values();

    // sync ke pivot (proposal_reviewers)
    $proposal->reviewers()->sync($reviewers);

    // update status proposal
    $proposal->status = 'Perlu Direview';
    $proposal->save();

    // 🔔 Kirim notifikasi ke reviewer yang ditugaskan
    foreach ($reviewers as $reviewerId) {
        NotificationHelper::send(
            $reviewerId,
            'Proposal Baru Ditugaskan',
            'Anda telah ditugaskan untuk meninjau proposal: "' . $proposal->judul . '"',
            'info'
        );
    }

    return back()->with('success', 'Reviewer berhasil ditetapkan dan notifikasi dikirim.');
}



    /**
     * Approve Proposal → disetujui
     */
    public function approveProposal(Proposal $proposal)
    {
        $proposal->status = 'Disetujui';
        $proposal->save();

        // 🔔 Notifikasi ke pengaju
        NotificationHelper::send($proposal->user_id, 'Proposal Anda disetujui', 'Proposal Anda telah disetujui oleh reviewer/admin', 'success');

        return back()->with('success', 'Proposal disetujui dan notifikasi telah dikirim.');
    }

    /**
     * Tandai Proposal perlu revisi
     */
    public function reviseProposal(Proposal $proposal)
    {
        $proposal->status = 'Direvisi';
        $proposal->save();

        // 🔔 Notifikasi ke pengaju
        NotificationHelper::send($proposal->user_id, 'Proposal Anda perlu direvisi', 'Silakan periksa catatan review dan revisi proposal Anda', 'warning');

        return back()->with('success', 'Proposal ditandai perlu revisi dan notifikasi dikirim.');
    }
}
