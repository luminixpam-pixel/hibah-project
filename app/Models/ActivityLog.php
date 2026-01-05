<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'activity',
        'ip_address',
        'user_agent'
    ];

    /**
     * Relasi ke model User
     */
    public function user()
    {
        // Pastikan kolom di tabel activity_logs adalah user_id
        return $this->belongsTo(User::class, 'user_id');
    }
}
