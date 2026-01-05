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

    /**
     * Menampilkan daftar proposal milik user (Pengaju/Reviewer) atau semua (Admin)
     */
    public function index(Request $request)
    {
        $tahun = $request->get('tahun', date('Y'));
        $query = Proposal::whereYear('created_at', $tahun);

        // Admin melihat semua data
        if (auth()->user()->role === 'admin') {
            $proposals = $query->latest()->get();
        }
        // Pengaju & Reviewer hanya melihat proposal yang mereka buat sendiri
        else {
            $proposals = $query->where('user_id', auth()->id())->latest()->get();
        }

        $role = auth()->user()->role;
        return view('proposal.daftar_proposal', compact('proposals', 'tahun', 'role'));
    }

    public function create()
    {
        return redirect()->route('proposal.index');
    }

    /**
     * Proses simpan proposal (Bisa dilakukan Pengaju & Reviewer)
     */
    public function store(Request $request)
    {
        // Proteksi Role
        if (!in_array(auth()->user()->role, ['pengaju', 'reviewer'])) {
            abort(403, 'Anda tidak memiliki izin untuk mengunggah proposal.');
        }

        $request->validate([
            'judul'      => 'required|string|max:255',
            'nama_ketua' => 'required|string|max:255',
            'biaya'      => 'nullable|numeric',
            'anggota'    => 'nullable|array',
            'file'       => 'required|file|mimes:pdf,doc,docx|max:102400',
        ]);

        // Olah data anggota (Array ke JSON)
        $anggota = $request->input('anggota', []);
        $anggota = is_array($anggota) ? array_values(array_filter($anggota, fn($v) => trim((string)$v) !== '')) : [];
        $anggotaJson = !empty($anggota) ? json_encode($anggota, JSON_UNESCAPED_UNICODE) : null;

        try {
            $extension = $request->file('file')->getClientOriginalExtension();
            $cleanName = preg_replace('/[^A-Za-z0-9\-]/', '', $request->judul);
            $finalName = $cleanName . '_' . time() . '.' . $extension;

            $filePath = $request->file('file')->storeAs('proposal_files', $finalName, 'public');

            $proposal = Proposal::create([
                'judul'      => $request->judul,
                'nama_ketua' => $request->nama_ketua,
                'file_path'  => $filePath,
                'anggota'    => $anggotaJson,
                'biaya'      => $request->biaya,
                'status'     => 'Dikirim',
                'user_id'    => auth()->id(),
            ]);

            $this->logActivity('Mengajukan proposal baru: "' . $proposal->judul . '"');

            NotificationHelper::send(
                auth()->id(),
                'Proposal Berhasil Dikirim',
                'Proposal "' . $proposal->judul . '" telah diterima sistem.',
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
            abort(403, 'Akses ditolak.');
        }

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
            'anggota'    => 'nullable|array',
        ]);

        $anggota = $request->input('anggota', []);
        $anggota = is_array($anggota) ? array_values(array_filter($anggota, fn($v) => trim((string)$v) !== '')) : [];
        $anggotaJson = !empty($anggota) ? json_encode($anggota, JSON_UNESCAPED_UNICODE) : null;

        $oldStatus = $proposal->status;
        $proposal->judul = $request->judul;
        $proposal->nama_ketua = $request->nama_ketua;
        $proposal->biaya = $request->biaya;
        $proposal->anggota = $anggotaJson;

        if ($request->hasFile('file')) {
            if ($proposal->file_path && Storage::disk('public')->exists($proposal->file_path)) {
                Storage::disk('public')->delete($proposal->file_path);
            }
            $proposal->file_path = $request->file('file')->store('proposal_files', 'public');
        }

        if (in_array($oldStatus, ['Ditolak', 'Direvisi'])) {
            $proposal->status = 'Hasil Revisi';
        }

        $proposal->save();
        $this->logActivity('Memperbarui proposal ID: ' . $proposal->id);

        if ($proposal->status === 'Hasil Revisi') {
            return redirect()->route('monitoring.hasilRevisi')->with('success', 'Revisi berhasil disimpan.');
        }

        return redirect()->route('proposal.index')->with('success', 'Proposal diperbarui.');
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
        return view('proposal.tinjau-proposal', compact('proposal'));
    }

    /* --- MONITORING METHODS --- */

    /**
     * Daftar proposal yang menunggu penunjukan reviewer (Admin)
     * Atau daftar tugas bagi Reviewer (dengan proteksi conflict of interest)
     */
    public function proposalPerluDireview()
    {
        $user = auth()->user();
        $query = Proposal::where('status', 'Perlu Direview')->with('reviewers');

        // Proteksi: Reviewer tidak boleh melihat proposal miliknya sendiri di antrean tugas
        if ($user->role === 'reviewer') {
            $query->where('user_id', '!=', $user->id);
        }

        $proposals = $query->latest()->get();
        $reviewers = User::where('role', 'reviewer')->orderBy('name')->get();

        return view('proposal.proposal-perlu-direview', compact('proposals', 'reviewers'));
    }

    public function proposalSedangDireview()
    {
        $proposals = Proposal::where('status', 'Sedang Direview')
            ->with(['reviewers', 'reviews'])
            ->latest()
            ->get();

        return view('proposal.proposal-sedang-direview', compact('proposals'));
    }

    public function reviewSelesai()
    {
        $proposals = Proposal::where('status', 'Review Selesai')
            ->orWhereIn('status_pendanaan', ['Disetujui', 'Ditolak', 'Direvisi'])
            ->with(['user', 'reviews.reviewer'])
            ->latest('updated_at')
            ->get();

        return view('proposal.proposal-selesai', compact('proposals'));
    }

    public function proposalDisetujui()
    {
        $proposals = Proposal::where('status', 'Disetujui')->latest()->get();
        return view('proposal.proposal-disetujui', compact('proposals'));
    }

    public function proposalDitolak()
    {
        $proposals = Proposal::where('status', 'Ditolak')->latest()->get();
        return view('proposal.proposal-ditolak', compact('proposals'));
    }

    public function proposalDirevisi()
    {
        $proposals = Proposal::whereIn('status', ['Ditolak', 'Direvisi'])->latest()->get();
        return view('proposal.proposal-direvisi', compact('proposals'));
    }

    public function hasilRevisi()
    {
        $proposals = Proposal::where('status', 'Hasil Revisi')->latest()->get();
        return view('proposal.hasil-review', compact('proposals'));
    }

    /**
     * Admin menugaskan reviewer (Diberikan proteksi agar reviewer tidak menilai diri sendiri)
     */
    public function assignReviewer(Request $request, $id)
    {
        $proposal = Proposal::findOrFail($id);

        $request->validate([
            'reviewer_1' => 'required|exists:users,id',
            'reviewer_2' => 'required|exists:users,id',
            'review_deadline' => 'required|date|after:now',
        ]);

        // CEK CONFLICT OF INTEREST
        if ($request->reviewer_1 == $proposal->user_id || $request->reviewer_2 == $proposal->user_id) {
            return back()->with('error', 'Reviewer tidak boleh pemilik dari proposal ini!');
        }

        if ($request->reviewer_1 == $request->reviewer_2) {
            return back()->with('error', 'Reviewer 1 dan 2 tidak boleh orang yang sama.');
        }

        $proposal->reviewers()->sync([$request->reviewer_1, $request->reviewer_2]);

        $proposal->update([
            'review_deadline' => Carbon::parse($request->review_deadline),
            'status' => 'Sedang Direview'
        ]);

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
        $this->logActivity('Menghapus proposal: ' . $proposal->judul);

        return back()->with('success', 'Proposal berhasil dihapus.');
    }

    public function setReview($id)
    {
        $proposal = Proposal::findOrFail($id);
        $proposal->update(['status' => 'Perlu Direview']);
        return redirect()->back()->with('success', 'Proposal masuk antrean review.');
    }

    public function keputusan(Request $request, $id)
    {
        $request->validate([
            'status_pendanaan' => 'required|in:Disetujui,Ditolak,Direvisi',
        ]);

        $proposal = Proposal::findOrFail($id);
        $proposal->update(['status_pendanaan' => $request->status_pendanaan]);

        return redirect()->back()->with('success', 'Keputusan disimpan.');
    }

    public function downloadReviewPdf($id)
    {
        $review = \App\Models\Review::with(['reviewer', 'proposal'])->findOrFail($id);
        $reviews = collect([$review]);
        $data = ['reviews' => $reviews, 'is_pdf' => true];

        $pdf = \Pdf::loadView('proposal.admin.hasil-review', $data);
        return $pdf->setPaper('a4', 'portrait')->stream('Hasil-Review-'.$id.'.pdf');
    }
    public function daftarReview(Request $request)
{
    $user = Auth::user();
    $query = Proposal::with(['user', 'reviewers']);

    if ($user->role === 'reviewer') {
        // HANYA menampilkan proposal yang ditugaskan ke reviewer ini
        $query->whereHas('reviewers', function($q) use ($user) {
            $q->where('reviewer_id', $user->id);
        });

        // Opsional: Hanya tampilkan yang statusnya sedang dalam tahap review
        $query->whereIn('status', ['Perlu Direview', 'Sedang Direview', 'Review Selesai']);
    }

    $proposals = $query->latest()->get();

    return view('admin.daftar_review', compact('proposals'));
}
}
