<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProposalReview extends Model
{
    protected $fillable = [
        'proposal_id', 'reviewer_id',
        'nilai_1','nilai_2','nilai_3','nilai_4','nilai_5','nilai_6','nilai_7',
        'total_score'
    ];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }
}

