<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\User;
use App\Models\Review;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Helpers\NotificationHelper;
use Carbon\Carbon;

class ProposalController extends Controller
{
    /**
     * Helper internal untuk mencatat log aktivitas pengguna
     */
    private function logActivity($activity)
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity' => $activity,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    public function index(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));
        $query = Proposal::whereYear('created_at', $tahun);

        if (auth()->user()->role === 'admin') {
            $proposals = $query->latest()->get();
        } else {
            $proposals = $query->where('user_id', auth()->id())->latest()->get();
        }

        return view('proposal.daftar_proposal', compact('proposals', 'tahun'));
    }

    public function create()
    {
        return redirect()->route('proposal.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul'      => 'required|string|max:255',
            'nama_ketua' => 'required|string|max:255',
            'biaya'      => 'nullable|numeric',
            'anggota'    => 'nullable|array',
            'file'       => 'required|file|mimes:pdf,doc,docx|max:102400',
        ]);

        try {
            $extension = $request->file('file')->getClientOriginalExtension();
            $cleanName = preg_replace('/[^A-Za-z0-9\-]/', '', $request->judul);
            $finalName = $cleanName . '_' . time() . '.' . $extension;

            $filePath = $request->file('file')->storeAs('proposal_files', $finalName, 'public');

            $proposal = Proposal::create([
                'judul'           => $request->judul,
                'nama_ketua'      => $request->nama_ketua,
                'file_path'       => $filePath,
                'anggota'         => $request->anggota,
                'biaya'           => $request->biaya,
                'status'          => 'Dikirim',
                'user_id'         => auth()->id(),
            ]);

            $this->logActivity('Mengajukan proposal baru: "' . $proposal->judul . '"');

            NotificationHelper::send(
                auth()->id(),
                'Proposal Anda berhasil dikirim',
                'Proposal Anda telah dikirim dan menunggu proses review',
                'success'
            );

            return redirect()->route('proposal.index')->with('success', 'Proposal berhasil diajukan!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal upload proposal: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $proposal = Proposal::findOrFail($id);

        if (auth()->user()->role !== 'admin' && auth()->user()->id !== $proposal->user_id) {
            abort(403, 'Anda tidak memiliki hak akses untuk mengedit proposal ini.');
        }

        // Disesuaikan dengan nama file Anda: edit-proposal.blade.php
        return view('proposal.edit-proposal', compact('proposal'));
    }

    public function update(Request $request, $id)
    {
        $proposal = Proposal::findOrFail($id);

        if (auth()->id() !== $proposal->user_id && auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'judul'      => 'required|string|max:255',
            'nama_ketua' => 'required|string|max:255',
            'biaya'      => 'nullable|numeric',
            'file'       => 'nullable|file|mimes:pdf,doc,docx|max:102400',
        ]);

        $oldStatus = $proposal->status;

        $proposal->judul = $request->judul;
        $proposal->nama_ketua = $request->nama_ketua;
        $proposal->biaya = $request->biaya;
        $proposal->anggota = $request->anggota;

        if ($request->hasFile('file')) {
            if ($proposal->file_path && Storage::disk('public')->exists($proposal->file_path)) {
                Storage::disk('public')->delete($proposal->file_path);
            }
            $filePath = $request->file('file')->store('proposal_files', 'public');
            $proposal->file_path = $filePath;
        }

        if (in_array($oldStatus, ['Ditolak', 'Direvisi'])) {
            $proposal->status = 'Hasil Revisi';
        }

        $proposal->save();
        $this->logActivity('Memperbarui proposal ID: ' . $proposal->id);

        if ($proposal->status === 'Hasil Revisi') {
            return redirect()->route('monitoring.hasilRevisi')->with('success', 'Revisi berhasil disimpan.');
        }

        return redirect()->route('proposal.index')->with('success', 'Proposal berhasil diperbarui.');
    }

    public function download($id)
    {
        $proposal = Proposal::findOrFail($id);
        $path = storage_path('app/public/' . $proposal->file_path);
        if (!file_exists($path)) return back()->with('error', 'File tidak ditemukan.');

        $this->logActivity('Mendownload file proposal ID: ' . $id);
        return response()->download($path);
    }

    public function tinjau($id)
    {
        $proposal = Proposal::with(['user', 'reviewers', 'reviews.reviewer'])->findOrFail($id);
        // Disesuaikan dengan nama file Anda: tinjau-proposal.blade.php
        return view('proposal.tinjau-proposal', compact('proposal'));
    }

    /* --- MONITORING METHODS --- */

    public function proposalPerluDireview()
    {
        $proposals = Proposal::where('status', 'Perlu Direview')
            ->with('reviewers')
            ->latest()
            ->get();

        $reviewers = User::where('role', 'reviewer')->orderBy('name')->get();

        // Disesuaikan dengan nama file Anda: proposal-perlu-direview.blade.php
        return view('proposal.proposal-perlu-direview', compact('proposals', 'reviewers'));
    }

    public function proposalSedangDireview()
    {
        $proposals = Proposal::where('status', 'Sedang Direview')
            ->with(['reviewers', 'reviews'])
            ->latest()
            ->get();

        // Disesuaikan dengan nama file Anda: proposal-sedang-direview.blade.php
        return view('proposal.proposal-sedang-direview', compact('proposals'));
    }

    public function reviewSelesai()
    {
        $proposals = Proposal::with(['user', 'reviewers', 'reviews.reviewer'])
            ->whereHas('reviews')
            ->whereNotIn('status', ['Disetujui', 'Ditolak'])
            ->latest()
            ->get();

        // Disesuaikan dengan nama file Anda: proposal-selesai.blade.php
        return view('proposal.proposal-selesai', compact('proposals'));
    }

    public function proposalDisetujui()
    {
        $proposals = Proposal::where('status', 'Disetujui')->latest()->get();
        // Disesuaikan dengan nama file Anda: proposal-disetujui.blade.php
        return view('proposal.proposal-disetujui', compact('proposals'));
    }

    public function proposalDitolak()
    {
        $proposals = Proposal::where('status', 'Ditolak')->latest()->get();
        // Disesuaikan dengan nama file Anda: proposal-ditolak.blade.php
        return view('proposal.proposal-ditolak', compact('proposals'));
    }

    public function proposalDirevisi()
    {
        $proposals = Proposal::whereIn('status', ['Ditolak', 'Direvisi'])->latest()->get();
        // Disesuaikan dengan nama file Anda: proposal-direvisi.blade.php
        return view('proposal.proposal-direvisi', compact('proposals'));
    }

    public function hasilRevisi()
    {
        $proposals = Proposal::where('status', 'Hasil Revisi')->latest()->get();
        // Disesuaikan dengan nama file Anda: hasil-review.blade.php
        return view('proposal.hasil-review', compact('proposals'));
    }

    public function assignReviewer(Request $request, Proposal $proposal)
    {
        $request->validate([
            'reviewer_1' => 'nullable|exists:users,id',
            'reviewer_2' => 'nullable|exists:users,id',
        ]);

        $reviewers = collect([$request->reviewer_1, $request->reviewer_2])->filter()->unique()->values();
        $proposal->reviewers()->sync($reviewers);

        $proposal->status = 'Perlu Direview';
        $proposal->review_deadline = Carbon::now()->addDays(7);
        $proposal->save();

        $this->logActivity('Menugaskan reviewer untuk proposal ID: ' . $proposal->id);

        return back()->with('success', 'Reviewer berhasil ditugaskan.');
    }

    public function destroy($id)
    {
        $proposal = Proposal::findOrFail($id);

        if (auth()->id() !== $proposal->user_id && auth()->user()->role !== 'admin') {
            abort(403, 'Akses ditolak.');
        }

        if ($proposal->file_path && Storage::disk('public')->exists($proposal->file_path)) {
            Storage::disk('public')->delete($proposal->file_path);
        }

        $proposal->delete();
        $this->logActivity('Menghapus permanen proposal: ' . $proposal->judul);

        return back()->with('success', 'Proposal berhasil dihapus.');
    }
}
