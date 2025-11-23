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
        'reviewer',
    ];
}
