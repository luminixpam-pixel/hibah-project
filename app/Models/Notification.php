<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User; // <--- tambahkan ini

class Notification extends Model
{
    use HasFactory;

    // WAJIB jika tabel sudah diganti dari 'notifications' menjadi 'review_selesai'
    protected $table = 'review_selesai';

    protected $fillable = ['user_id', 'title', 'type', 'is_read'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
 