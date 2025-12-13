<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

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

    /* ===================== REVIEWER ===================== */

    public function isiReview($id)
    {
        $proposal = Proposal::findOrFail($id);

      if (Auth::user()->role === 'reviewer') {
    if (!$proposal->reviewers->pluck('id')->contains(Auth::id())) {
        abort(403, 'Anda bukan reviewer yang ditugaskan.');
    }
}

        if ($proposal->status === 'Perlu Direview') {
            $proposal->update(['status' => 'Sedang Direview']);
        }

        return view('reviewer.isi-review', compact('proposal'));
    }

    public function submitReview(Request $request, $id)
    {
        $proposal = Proposal::findOrFail($id);

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

        Review::create([
            'proposal_id' => $proposal->id,
            'reviewer_id' => Auth::id(),
            'nilai_1'     => $request->nilai_1,
            'nilai_2'     => $request->nilai_2,
            'nilai_3'     => $request->nilai_3,
            'nilai_4'     => $request->nilai_4,
            'nilai_5'     => $request->nilai_5,
            'nilai_6'     => $request->nilai_6,
            'nilai_7'     => $request->nilai_7,
            'catatan'     => $request->catatan,
            'total_score' => $totalScore,
        ]);

        $proposal->update([
            'status' => match ($request->status) {
                'disetujui' => 'Disetujui',
                'ditolak'   => 'Ditolak',
                'direvisi'  => 'Direvisi',
                default     => 'Review Selesai',
            }
        ]);

        return redirect()->back()->with('success', 'Review berhasil disimpan.');
    }
}
