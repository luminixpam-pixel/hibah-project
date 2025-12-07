<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $table = 'reviews';

    protected $fillable = [
        'proposal_id',
        'reviewer_id',
        'nilai_1',
        'nilai_2',
        'nilai_3',
        'nilai_4',
        'nilai_5',
        'nilai_6',
        'nilai_7',
        'status',      // kalau kolom ini ada di tabel
        'catatan',     // kalau kolom ini ada di tabel
        'total_score', // ⬅️ penting
    ];

    public function proposal()
    {
        return $this->belongsTo(Proposal::class);
    }
}
