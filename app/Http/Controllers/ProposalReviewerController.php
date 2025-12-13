<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Models\User;
use Illuminate\Http\Request;

class ProposalReviewerController extends Controller
{
    // 🔍 SEARCH REVIEWER (buat autocomplete)
    public function search(Request $request)
    {
        return User::where('role', 'reviewer')
            ->where('name', 'like', '%' . $request->q . '%')
            ->limit(10)
            ->get(['id', 'name', 'penempatan']);
    }

    // 💾 SIMPAN 2 REVIEWER KE PROPOSAL
    public function assign(Request $request, $proposalId)
    {
        $request->validate([
            'reviewer_1' => 'required|exists:users,id',
            'reviewer_2' => 'required|exists:users,id|different:reviewer_1',
        ]);

        $proposal = Proposal::findOrFail($proposalId);

        // maksimal 2 reviewer
        $proposal->reviewers()->sync([
            $request->reviewer_1,
            $request->reviewer_2,
        ]);

        return back()->with('success', 'Reviewer berhasil ditetapkan');
    }
}
