<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Notification; // ⬅️ WAJIB INI
use App\Helpers\NotificationHelper; // ✅ TAMBAH INI (buat notif bell)

class ReviewerController extends Controller
{
    /**
     * Ambil daftar reviewer (buat search di admin) - DIPANGGIL VIA AJAX
     */
    public function searchReviewer(Request $request)
    {
        $q = $request->q;

        // ✅ ADMIN: boleh cari user siapa saja
        if (Auth::check() && Auth::user()->role === 'admin') {
            $users = User::where('name', 'like', "%$q%")
                ->select('id', 'name', 'role')
                ->limit(10)
                ->get();

            return response()->json($users);
        }

        // ✅ DEFAULT: hanya role reviewer
        $reviewers = User::where('role', 'reviewer')
            ->where('name', 'like', "%$q%")
            ->select('id', 'name')
            ->limit(10)
            ->get();

        return response()->json($reviewers);
    }

    /**
     * ✅ ADMIN: halaman list user untuk dijadikan reviewer
     * ✅ REVIEWER: halaman list proposal yang ditugaskan
     */
    public function index()
    {
        if (Auth::check() && Auth::user()->role === 'admin') {
            $users = User::orderBy('name')->get();
            return view('reviewer.pilih-reviewer', compact('users'));
        }

        // ✅ kalau reviewer buka menu reviewer
        $proposals = Proposal::with('reviewers')
            ->whereHas('reviewers', function ($q) {
                $q->where('users.id', Auth::id());
            })
            ->orderBy('review_deadline', 'asc')
            ->get();

        return view('proposal-perlu-direview', compact('proposals'));
    }

    /**
     * ✅ ADMIN bisa jadikan user sebagai reviewer
     * route: POST /admin/reviewer/{user}
     */
    public function setReviewer(User $user)
    {
        // 🔒 pastikan admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        // admin tidak boleh diubah
        if ($user->role === 'admin') {
            return back()->with('error', 'Admin tidak bisa dijadikan reviewer.');
        }

        // ✅ kalau sudah reviewer, stop aja
        if ($user->role === 'reviewer') {
            return back()->with('error', 'User ini sudah menjadi reviewer.');
        }

        $user->role = 'reviewer';
        $user->save();

        // ✅ kirim notif bell
        NotificationHelper::send(
            $user->id,
            'Anda Ditugaskan Sebagai Reviewer',
            'Role akun Anda telah diubah menjadi Reviewer oleh Admin. Anda tidak dapat mengunggah proposal selama berstatus Reviewer.',
            'info'
        );

        return back()->with('success', 'User berhasil dijadikan reviewer.');
    }

    /**
     * ✅ ADMIN bisa berhentikan reviewer (balikin role jadi pengaju)
     * route: POST /admin/reviewer/{user}/remove
     */
    public function removeReviewer(User $user)
    {
        // 🔒 pastikan admin
        if (!Auth::check() || Auth::user()->role !== 'admin') {
            abort(403);
        }

        // ✅ kalau bukan reviewer, stop
        if ($user->role !== 'reviewer') {
            return back()->with('error', 'User ini bukan reviewer.');
        }

        $user->role = 'pengaju';
        $user->save();

        // ✅ kirim notif bell
        NotificationHelper::send(
            $user->id,
            'Status Reviewer Dihentikan',
            'Role akun Anda telah dikembalikan menjadi Pengaju oleh Admin. Anda sekarang bisa mengunggah proposal.',
            'success'
        );

        return back()->with('success', 'Reviewer berhasil dihentikan (role dikembalikan jadi pengaju).');
    }

    /**
     * Halaman isi review oleh reviewer
     */
    public function isiReview($id)
    {
        $proposal = Proposal::with('reviewers')->findOrFail($id);

        $userId = Auth::user()->id;

        // ✅ Pastikan reviewer benar-benar ditugaskan
        if (Auth::user()->role === 'reviewer') {
            $isAssigned = $proposal->reviewers->pluck('id')->contains($userId);

            if (!$isAssigned) {
                abort(403, 'Anda bukan reviewer yang ditugaskan. (ID Anda: '.$userId.')');
            }
        }

        return view('reviewer.isi-review', compact('proposal'));
    }

    /**
     * Submit review oleh reviewer
     */
    public function submitReview(Request $request, $id)
    {
        $proposal = Proposal::with('reviewers')->findOrFail($id);

        // ✅ validasi
        $request->validate([
            'nilai_1' => 'required|integer|min:0|max:5',
            'nilai_2' => 'required|integer|min:0|max:5',
            'nilai_3' => 'required|integer|min:0|max:5',
            'nilai_4' => 'required|integer|min:0|max:5',
            'nilai_5' => 'required|integer|min:0|max:5',
            'nilai_6' => 'required|integer|min:0|max:5',
            'nilai_7' => 'required|integer|min:0|max:5',
            'catatan' => 'nullable|string',
        ]);

        // ✅ bobot (sesuaikan kalau berbeda)
        $bobot = [5, 5, 5, 3, 5, 5, 10];

        $totalSkorMurni = 0;
        for ($i = 1; $i <= 7; $i++) {
            $skorMurni = $request->input("nilai_$i");
            $totalSkorMurni += ($skorMurni * $bobot[$i-1]);
        }

        // maksimum murni = 190 (berdasarkan bobot di atas)
        $maxMurni = 190;

        // konversi ke skala 500
        $finalScore500 = round(($totalSkorMurni / $maxMurni) * 500);

        // simpan review
        Review::create([
            'proposal_id' => $proposal->id,
            'reviewer_id' => Auth::user()->id,
            'nilai_1'     => $request->nilai_1,
            'nilai_2'     => $request->nilai_2,
            'nilai_3'     => $request->nilai_3,
            'nilai_4'     => $request->nilai_4,
            'nilai_5'     => $request->nilai_5,
            'nilai_6'     => $request->nilai_6,
            'nilai_7'     => $request->nilai_7,
            'total_score' => $finalScore500,
            'catatan'     => $request->catatan,
        ]);

        // update status proposal
        $this->updateProposalStatus($proposal);

        return redirect()->route('proposal.index')->with('success', 'Review berhasil dikirim dengan skor: ' . $finalScore500);
    }

    /**
     * Update status proposal setelah review masuk
     */
    private function updateProposalStatus($proposal)
    {
        $jumlahReviewer = $proposal->reviewers->count();
        $jumlahReviewMasuk = Review::where('proposal_id', $proposal->id)->count();

        if ($jumlahReviewer > 0 && $jumlahReviewMasuk >= $jumlahReviewer) {
            $proposal->update(['status' => 'Review Selesai']);
        } else {
            $proposal->update(['status' => 'Sedang Direview']);
        }
    }
}
