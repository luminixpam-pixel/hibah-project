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
     * Ambil daftar reviewer (buat search di admin)
     * DIPANGGIL VIA AJAX
     */
    public function searchReviewer(Request $request)
    {
        $q = $request->q;

        // ✅ ADMIN: boleh cari user siapa saja (kecuali admin), biar bisa dipilih jadi reviewer
        if (Auth::check() && Auth::user()->role === 'admin') {
            $users = User::where('role', '!=', 'admin')
                ->where('name', 'like', "%$q%")
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
        // ✅ kalau admin buka /admin/reviewer → tampil list user
        if (Auth::check() && Auth::user()->role === 'admin') {

            // tampilkan semua user kecuali admin (biar admin gak bisa jadi reviewer)
            $users = User::where('role', '!=', 'admin')
                ->orderBy('name')
                ->get();

            // ✅ sesuai lokasi file lo:
            // resources/views/reviewer/pilih-reviewer.blade.php
            return view('reviewer.pilih-reviewer', compact('users'));
        }

        // ✅ kalau reviewer buka menu reviewer (tetap logic lama kamu)
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

        // ✅ jadikan reviewer
        $user->role = 'reviewer';
        $user->save();

        // ✅ NOTIF (bell) ke user yang diubah rolenya
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

        // admin tidak boleh diubah
        if ($user->role === 'admin') {
            return back()->with('error', 'Admin tidak bisa diubah.');
        }

        // kalau bukan reviewer, ngapain
        if ($user->role !== 'reviewer') {
            return back()->with('error', 'User ini bukan reviewer.');
        }

        // ✅ balikin jadi pengaju
        $user->role = 'pengaju';
        $user->save();

        // ✅ NOTIF (bell) ke user yang diubah rolenya
        NotificationHelper::send(
            $user->id,
            'Status Reviewer Dihentikan',
            'Role akun Anda telah dikembalikan menjadi Pengaju oleh Admin. Anda sekarang bisa mengunggah proposal.',
            'success'
        );

        return back()->with('success', 'Reviewer berhasil dihentikan (role dikembalikan jadi pengaju).');
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
            'status'  => 'nullable|string',
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

        // ✅ LOCK: kalau reviewer ini sudah submit untuk proposal ini, stop
        $alreadySubmitted = Review::where('proposal_id', $proposal->id)
            ->where('reviewer_id', Auth::id())
            ->exists();

        if ($alreadySubmitted) {
            return redirect()->back()->with('error', 'Anda sudah submit review untuk proposal ini. Review tidak bisa diubah lagi.');
        }

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

        $jumlahReviewMasuk = Review::where('proposal_id', $proposal->id)
            ->distinct('reviewer_id')
            ->count('reviewer_id');

        if ($jumlahReviewerDitugaskan > 0 && $jumlahReviewMasuk >= $jumlahReviewerDitugaskan) {
            $proposal->update(['status' => 'Review Selesai']);
        } else {
            $proposal->update(['status' => 'Sedang Direview']);
        }

        return redirect()->back()->with('success', 'Review berhasil disimpan.');
    }
}
