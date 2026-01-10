<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Template extends Model
{
    use HasFactory;

    /**
     * Nama tabel di database (opsional jika nama tabel adalah 'templates')
     */
    protected $table = 'templates';

    /**
     * Kolom yang dapat diisi secara massal.
     */
    protected $fillable = [
        'nama_template',
        'file_path',
        'jenis'
    ];

    /**
     * Accessor: Mendapatkan URL lengkap file template.
     * Contoh penggunaan di Blade: {{ $template->file_url }}
     */
    public function getFileUrlAttribute()
    {
        if ($this->file_path && Storage::disk('public')->exists($this->file_path)) {
            return asset('storage/' . $this->file_path);
        }

        return null;
    }

    /**
     * Scope: Memudahkan pencarian berdasarkan jenis template.
     * Contoh penggunaan di Controller: Template::jenis('laporan_kemajuan')->first();
     */
    public function scopeJenis($query, $jenis)
    {
        return $query->where('jenis', $jenis);
    }
}
