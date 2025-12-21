<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\User;
use App\Models\Review;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use App\Helpers\NotificationHelper;
use Carbon\Carbon;

class ProposalController extends Controller
{
    public function index()
    {
        $proposals = Proposal::where('status', 'Dikirim')
            ->latest()
            ->get();

        return view('proposal.daftar_proposal', compact('proposals'));
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

    public function tinjau($id)
    {
        $proposal = Proposal::findOrFail($id);
        return view('proposal.tinjau-proposal', compact('proposal'));
    }

    public function edit($id)
    {
        $proposal = Proposal::findOrFail($id);

        if (auth()->id() !== $proposal->user_id || auth()->user()->role !== 'pengaju') {
            abort(403, 'Anda tidak berhak mengedit proposal ini.');
        }

        return view('proposal.edit-proposal', compact('proposal'));
    }

    public function update(Request $request, $id)
    {
        $proposal = Proposal::findOrFail($id);

        if (auth()->id() !== $proposal->user_id || auth()->user()->role !== 'pengaju') {
            abort(403, 'Anda tidak berhak mengedit proposal ini.');
        }

        // ✅ simpan status sebelum update (buat logic pindah ke Hasil Revisi)
        $oldStatus = $proposal->status;

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

        // ✅ Kalau proposal ini sebelumnya Ditolak/Direvisi dan user sudah simpan perubahan,
        // otomatis masuk ke "Hasil Revisi"
        if (in_array($oldStatus, ['Ditolak', 'Direvisi'])) {
            $proposal->status = 'Hasil Revisi';
        }

        $proposal->save();

        // ✅ kalau habis revisi, langsung arahkan ke menu Hasil Revisi
        if (in_array($oldStatus, ['Ditolak', 'Direvisi'])) {
            return redirect()->route('monitoring.hasilRevisi')->with('success', 'Revisi berhasil disimpan dan masuk ke Hasil Revisi.');
        }

        return redirect()->route('proposal.index')->with('success', 'Proposal berhasil diperbarui.');
    }

    public function moveToPerluDireview(Proposal $proposal)
    {
        if ($proposal->status !== 'Dikirim') {
            return back()->with('error', 'Proposal ini tidak dalam status Dikirim.');
        }

        $proposal->status = 'Perlu Direview';
        $proposal->save();

        NotificationHelper::send(
            $proposal->user_id,
            'Proposal Anda sedang direview',
            'Proposal Anda kini masuk antrian review',
            'info'
        );

        return back()->with('success', 'Proposal berhasil dipindahkan ke "Perlu Direview".');
    }

    public function proposalPerluDireview()
    {
        $proposals = Proposal::where('status', 'Perlu Direview')
            ->with('reviewers')
            ->latest()
            ->get();

        $reviewers = User::where('role', 'reviewer')
            ->orderBy('name')
            ->get();

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
        $proposalIds = Proposal::withCount([
                'reviewers',
                'reviews as reviews_done_count' => function ($q) {
                    $q->select(DB::raw('COUNT(DISTINCT reviewer_id)'));
                }
            ])
            ->having('reviewers_count', '>=', 2)
            ->havingRaw('reviews_done_count >= reviewers_count')
            ->pluck('id');

        $proposals = Proposal::with(['user', 'reviewers', 'reviews.reviewer'])
            ->whereIn('id', $proposalIds)
            ->whereNotIn('status', ['Disetujui', 'Ditolak'])
            ->orderByDesc('updated_at')
            ->get();

        return view('proposal.proposal-selesai', compact('proposals'));
    }

    public function proposalDisetujui()
    {
        $proposalIds = Proposal::withCount([
                'reviewers',
                'reviews as reviews_done_count' => function ($q) {
                    $q->select(DB::raw('COUNT(DISTINCT reviewer_id)'));
                }
            ])
            ->having('reviewers_count', '>=', 2)
            ->havingRaw('reviews_done_count >= reviewers_count')
            ->pluck('id');

        $proposals = Proposal::with(['user', 'reviewers', 'reviews.reviewer'])
            ->whereIn('id', $proposalIds)
            ->where('status', 'Disetujui')
            ->orderByDesc('updated_at')
            ->get();

        return view('proposal.proposal-disetujui', compact('proposals'));
    }

    public function proposalDitolak()
    {
        $proposalIds = Proposal::withCount([
                'reviewers',
                'reviews as reviews_done_count' => function ($q) {
                    $q->select(DB::raw('COUNT(DISTINCT reviewer_id)'));
                }
            ])
            ->having('reviewers_count', '>=', 2)
            ->havingRaw('reviews_done_count >= reviewers_count')
            ->pluck('id');

        $proposals = Proposal::with(['user', 'reviewers', 'reviews.reviewer'])
            ->whereIn('id', $proposalIds)
            ->where('status', 'Ditolak')
            ->orderByDesc('updated_at')
            ->get();

        return view('proposal.proposal-ditolak', compact('proposals'));
    }

    public function proposalDirevisi()
    {
        $proposalIds = Proposal::withCount([
                'reviewers',
                'reviews as reviews_done_count' => function ($q) {
                    $q->select(DB::raw('COUNT(DISTINCT reviewer_id)'));
                }
            ])
            ->having('reviewers_count', '>=', 2)
            ->havingRaw('reviews_done_count >= reviewers_count')
            ->pluck('id');

        $proposals = Proposal::with(['user', 'reviewers', 'reviews.reviewer'])
            ->whereIn('id', $proposalIds)
            ->whereIn('status', ['Ditolak', 'Direvisi'])
            ->orderByDesc('updated_at')
            ->get();

        return view('proposal.proposal-direvisi', compact('proposals'));
    }

    public function hasilRevisi()
    {
        $proposals = Proposal::with(['user', 'reviewers', 'reviews.reviewer'])
            ->where('status', 'Hasil Revisi')
            ->orderByDesc('updated_at')
            ->get();

        return view('proposal.hasil-review', compact('proposals'));
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

        $proposal->reviewers()->sync($reviewers);

        $proposal->status = 'Perlu Direview';
        $proposal->review_deadline = Carbon::now()->addDays(7);
        $proposal->save();

        foreach ($reviewers as $reviewerId) {
            NotificationHelper::send(
                $reviewerId,
                'Proposal Baru Ditugaskan',
                'Deadline review: ' . Carbon::parse($proposal->review_deadline)->format('d M Y'),
                'info'
            );
        }

        return back()->with('success', 'Reviewer & deadline berhasil ditetapkan.');
    }

    public function approveProposal(Proposal $proposal)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Hanya admin yang boleh menyetujui proposal.');
        }

        $proposal->status = 'Disetujui';
        $proposal->save();

        NotificationHelper::send(
            $proposal->user_id,
            'Proposal Disetujui',
            'Proposal "' . $proposal->judul . '" telah disetujui oleh admin.',
            'success'
        );

        return back()->with('success', 'Proposal berhasil disetujui.');
    }

    public function rejectProposal(Proposal $proposal)
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Hanya admin yang boleh menolak proposal.');
        }

        $proposal->status = 'Ditolak';
        $proposal->save();

        NotificationHelper::send(
            $proposal->user_id,
            'Proposal Ditolak',
            'Proposal "' . $proposal->judul . '" telah ditolak oleh admin.',
            'error'
        );

        return back()->with('success', 'Proposal berhasil ditolak.');
    }

    public function reviseProposal(Proposal $proposal)
    {
        $proposal->status = 'Direvisi';
        $proposal->save();

        NotificationHelper::send(
            $proposal->user_id,
            'Proposal Anda perlu direvisi',
            'Silakan periksa catatan review dan revisi proposal Anda',
            'warning'
        );

        return back()->with('success', 'Proposal ditandai perlu revisi dan notifikasi dikirim.');
    }

    public function downloadReviewPdf(Review $review)
    {
        $proposal = Proposal::findOrFail($review->proposal_id);

        $penilaian = [
            ['kriteria' => 'Kesesuaian Tema', 'nilai' => $review->nilai_1],
            ['kriteria' => 'Latar Belakang', 'nilai' => $review->nilai_2],
            ['kriteria' => 'Tujuan Kegiatan', 'nilai' => $review->nilai_3],
            ['kriteria' => 'Metodologi', 'nilai' => $review->nilai_4],
            ['kriteria' => 'Luaran', 'nilai' => $review->nilai_5],
            ['kriteria' => 'Anggaran', 'nilai' => $review->nilai_6],
            ['kriteria' => 'Kelayakan Proposal', 'nilai' => $review->nilai_7],
        ];

        $pdf = Pdf::loadView('pdf.hasil_penilaian', [
            'review' => $review,
            'proposal' => $proposal,
            'penilaian' => $penilaian,
        ]);

        return $pdf->download('hasil-penilaian-proposal.pdf');
    }
}
