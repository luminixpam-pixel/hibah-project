<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Notification; // ⬅️ WAJIB INI




class ReviewerController extends Controller
{
    /**
     * Ambil daftar reviewer (buat search di admin)
     * DIPANGGIL VIA AJAX
     */
    public function searchReviewer(Request $request)
    {
        $q = $request->q;

        $reviewers = User::where('role', 'reviewer')
            ->where('name', 'like', "%$q%")
            ->select('id', 'name')
            ->limit(10)
            ->get();

        return response()->json($reviewers);
    }
    public function index()
    {
        $proposals = Proposal::with('reviewers')
            ->whereHas('reviewers', function ($q) {
                $q->where('users.id', Auth::id());
            })
            ->orderBy('review_deadline', 'asc')
            ->get();

        return view('proposal-perlu-direview', compact('proposals'));
    }


    /* ===================== REVIEWER ===================== */
public function isiReview($id)
{
    $proposal = Proposal::with('reviewers')->findOrFail($id);

    // validasi reviewer
    if (Auth::user()->role === 'reviewer') {
        if (!$proposal->reviewers->pluck('id')->contains(Auth::id())) {
            abort(403, 'Anda bukan reviewer yang ditugaskan.');
        }
    }

    // 🔔 TANDAI NOTIFIKASI CUSTOM SEBAGAI DIBACA
    Notification::where('user_id', Auth::id())
        ->where('proposal_id', $proposal->id)
        ->where('is_read', false)
        ->update(['is_read' => true]);

    // 🔄 Update status proposal
    if ($proposal->status === 'Perlu Direview') {
        $proposal->update(['status' => 'Sedang Direview']);
    }

    return view('reviewer.isi-review', compact('proposal'));
}


    public function submitReview(Request $request, $id)
    {
        $proposal = Proposal::with('reviewers')->findOrFail($id);

        if (Auth::user()->role === 'reviewer') {
            if (!$proposal->reviewers->pluck('id')->contains(Auth::id())) {
                abort(403, 'Anda bukan reviewer yang ditugaskan.');
            }
        }

        $request->validate([
            'nilai_1' => 'nullable|integer|min:0|max:5',
            'nilai_2' => 'nullable|integer|min:0|max:5',
            'nilai_3' => 'nullable|integer|min:0|max:5',
            'nilai_4' => 'nullable|integer|min:0|max:5',
            'nilai_5' => 'nullable|integer|min:0|max:5',
            'nilai_6' => 'nullable|integer|min:0|max:5',
            'nilai_7' => 'nullable|integer|min:0|max:5',
            'catatan' => 'nullable|string',
            'status'  => 'nullable|string', // status dari form (dipakai untuk finalStatus)
        ]);

        $totalScore = collect([
            $request->nilai_1,
            $request->nilai_2,
            $request->nilai_3,
            $request->nilai_4,
            $request->nilai_5,
            $request->nilai_6,
            $request->nilai_7,
        ])->filter()->sum();

        // ✅ LOCK: kalau reviewer ini sudah submit untuk proposal ini, stop (tidak boleh submit ulang)
        $alreadySubmitted = Review::where('proposal_id', $proposal->id)
            ->where('reviewer_id', Auth::id())
            ->exists();

        if ($alreadySubmitted) {
            return redirect()->back()->with('error', 'Anda sudah submit review untuk proposal ini. Review tidak bisa diubah lagi.');
        }

        // ✅ simpan review per reviewer (kalau sudah ada → update)
        // NOTE: kolom `status` di tabel reviews kamu BELUM ADA (error SQLSTATE[42S22]),
        // jadi JANGAN simpan status ke tabel reviews dulu.
        // Kalau nanti kamu sudah bikin migration add_status_to_reviews_table,
        // baru boleh tambahkan: 'status' => $request->status,
        Review::updateOrCreate(
            [
                'proposal_id' => $proposal->id,
                'reviewer_id' => Auth::id(),
            ],
            [
                'nilai_1'     => $request->nilai_1,
                'nilai_2'     => $request->nilai_2,
                'nilai_3'     => $request->nilai_3,
                'nilai_4'     => $request->nilai_4,
                'nilai_5'     => $request->nilai_5,
                'nilai_6'     => $request->nilai_6,
                'nilai_7'     => $request->nilai_7,
                'catatan'     => $request->catatan,
                'total_score' => $totalScore,
            ]
        );

        // ===========================
        // ✅ LOGIKA 2 REVIEWER WAJIB
        // ===========================
        $jumlahReviewerDitugaskan = $proposal->reviewers->count();

        // hitung jumlah review unik (per reviewer) untuk proposal ini
        $jumlahReviewMasuk = Review::where('proposal_id', $proposal->id)
            ->distinct('reviewer_id')
            ->count('reviewer_id');

        // Proposal baru "Review Selesai" kalau semua reviewer sudah submit
        if ($jumlahReviewerDitugaskan > 0 && $jumlahReviewMasuk >= $jumlahReviewerDitugaskan) {

            // final status baru ditentukan kalau 2 reviewer sudah submit
            $finalStatus = match ($request->status) {
                'disetujui' => 'Disetujui',
                'ditolak'   => 'Ditolak',
                'direvisi'  => 'Direvisi',
                default     => 'Review Selesai',
            };

            $proposal->update(['status' => $finalStatus]);

        } else {
            // masih menunggu reviewer lainnya
            $proposal->update(['status' => 'Sedang Direview']);
        }

        return redirect()->back()->with('success', 'Review berhasil disimpan.');
    }
}
