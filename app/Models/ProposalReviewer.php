<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalReviewer extends Model
{
    protected $fillable = [
        'proposal_id',
        'reviewer_id',
        'review_deadline',
    ];

    public function reviewers()
{
    return $this->belongsToMany(User::class, 'proposal_reviewers', 'proposal_id', 'reviewer_id');
}

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }
}



