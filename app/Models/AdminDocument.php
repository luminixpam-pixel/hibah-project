<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdminDocument extends Model
{
    protected $fillable = [
        'judul',
        'file_path',
        'uploaded_by',
        'is_visible', // ✅ tambah
    ];

    protected $casts = [
        'is_visible' => 'boolean', // ✅ biar true/false aman
    ];
}
