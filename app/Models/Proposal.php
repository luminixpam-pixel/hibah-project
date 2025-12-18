<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    protected $fillable = [
        'nama_ketua',
        'anggota',
        'biaya',
        'judul',
        'file_path',
        'status',
        'periode',
        'fakultas_prodi',
        'user_id',
        'pengusul',
        'review_deadline',
    ];

    public function reviewers()
    {
        return $this->belongsToMany(User::class, 'proposal_reviewers', 'proposal_id', 'reviewer_id');
    }

    // ✅ tambahan: relasi ke tabel reviews
    public function reviews()
    {
        return $this->hasMany(Review::class, 'proposal_id');
    }

    protected $casts = [
        'review_deadline' => 'datetime',
    ];
    }
