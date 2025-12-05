<?php

// app/Models/Proposal.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proposal extends Model
{
    use HasFactory;

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
        'reviewer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }
}

