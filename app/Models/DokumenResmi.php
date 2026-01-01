<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class DokumenResmi extends Model
{
    protected $table = 'dokumen_resmi';

    protected $fillable = [
        'judul',
        'kategori',
        'deskripsi',
        'file_path',
        'uploaded_by'
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

