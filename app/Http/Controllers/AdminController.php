<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\User;
use App\Notifications\ProposalAssignedNotification;

class AdminController extends Controller
{
    /**
     * Halaman hasil review
     */
    public function hasilReview()
    {
        $reviews = []; // contoh
        return view('admin.hasil-review', compact('reviews'));
    }

    /**
     * Halaman kalender
     */
    public function calendar()
    {
        return view('admin.calendar');
    }

    /**
     * ================================
     * ASSIGN REVIEWER + TENGGAT REVIEW
     * ================================
     */
    public function assignReviewer(Request $request, $id)
    {
        // 🔒 pastikan admin
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        // ✅ validasi
        $request->validate([
            'reviewer_1'      => 'nullable|exists:users,id',
            'reviewer_2'      => 'nullable|exists:users,id|different:reviewer_1',
            'review_deadline' => 'required|date|after:now',
        ]);

        $proposal = Proposal::with('reviewers')->findOrFail($id);

        // kumpulkan reviewer (hindari null)
        $reviewers = array_filter([
            $request->reviewer_1,
            $request->reviewer_2,
        ]);

        // simpan reviewer (pivot)
        $proposal->reviewers()->sync($reviewers);

        // simpan tenggat & status
        $proposal->update([
            'review_deadline' => $request->review_deadline,
            'status'          => 'Perlu Direview',
        ]);

        // 🔔 kirim notifikasi ke reviewer
        $users = User::whereIn('id', $reviewers)->get();

        foreach ($users as $user) {
            $user->notify(new ProposalAssignedNotification($proposal));
        }

        return back()->with('success', 'Reviewer dan tenggat review berhasil ditetapkan.');
    }
}
