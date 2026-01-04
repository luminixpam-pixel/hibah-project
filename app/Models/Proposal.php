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
        'file_laporan',
        'keterangan',
        'status_pendanaan',

    ];

    public function reviewers()
    {
        return $this->belongsToMany(User::class, 'proposal_reviewers', 'proposal_id', 'reviewer_id');
    }

    //relasi ke tabel reviews
    public function reviews()
    {
        return $this->hasMany(Review::class, 'proposal_id');
    }

    //relasi ke pengaju (users)
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    protected $casts = [
        'review_deadline' => 'datetime',
    ];


}
