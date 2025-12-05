<?php

// app/Http/Controllers/ReviewerController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ReviewerController extends Controller
{
    public function isiReview($id)
    {
        $proposal = Proposal::findOrFail($id);
        return view('reviewer.isi-review', compact('proposal'));
    }

    public function submitReview(Request $request, $id)
    {
        $proposal = Proposal::findOrFail($id);

        $review = Review::create([
            'proposal_id' => $proposal->id,
            'reviewer_id' => Auth::id(),
            'nilai_1' => $request->nilai_1,
            'nilai_2' => $request->nilai_2,
            'nilai_3' => $request->nilai_3,
            'nilai_4' => $request->nilai_4,
            'nilai_5' => $request->nilai_5,
            'nilai_6' => $request->nilai_6,
            'nilai_7' => $request->nilai_7,
            'status' => $request->status, // disetujui/ditolak/pending
            'catatan' => $request->catatan,
        ]);

        return redirect()->route('reviewer.isi-review', $proposal->id)
                         ->with('success', 'Review berhasil disimpan.');
    }
}

