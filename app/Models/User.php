<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'nidn',
        'no_telepon',
        'fakultas',
        'program_studi',
        'jabatan',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /*
    |--------------------------------------------------------------------------
    | FORMAL ROLE LABEL
    |--------------------------------------------------------------------------
    | Mengubah role menjadi format yang lebih formal untuk tampilan.
    | Tidak mengubah database.
    */
    public function getRoleLabelAttribute()
    {
        return match ($this->role) {
            'admin' => 'Admin',
            'reviewer' => 'Reviewer Proposal',
            'pengaju' => 'Pengaju Penelitian',
            default => ucfirst($this->role),
        };
    }
     public function notifications()
    {
        return $this->hasMany(\App\Models\Notification::class, 'user_id');
    }
}
