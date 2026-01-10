<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Proposal extends Model
{
    use HasFactory;

    protected $table = 'proposals';

    protected $fillable = [
        'nama_ketua',
        'anggota',
        'biaya',
        'judul',
        'file_path',
        'status',
        'periode',
        'fakultas_prodi', // Ini akan menyimpan ID dari tabel fakultas
        'user_id',
        'pengusul',
        'review_deadline',
        'file_laporan',
        'keterangan',
        'status_pendanaan',
        'file_laporan',
        'keterangan',
        'laporan_kemajuan',
        'laporan_akhir',
        'file_laporan_akhir',
        'keterangan_akhir',
        'status_laporan'
    ];

    /**
     * Casting data agar otomatis dikonversi saat diakses
     */
    protected $casts = [
        'review_deadline' => 'datetime',
        'anggota' => 'array', // Mengonversi JSON di database menjadi Array PHP secara otomatis
        'biaya' => 'integer', // Memastikan biaya diproses sebagai angka
    ];

    /**
     * Relasi ke Tabel Fakultas
     * Menghubungkan kolom fakultas_prodi ke id di tabel fakultas
     */
    public function fakultas()
    {
        return $this->belongsTo(Fakultas::class, 'fakultas_prodi');
    }

    /**
     * Relasi ke Pengaju (User)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Reviewers (Many to Many)
     */
    public function reviewers()
    {
        return $this->belongsToMany(User::class, 'proposal_reviewers', 'proposal_id', 'reviewer_id');
    }

    /**
     * Relasi ke Tabel Reviews (One to Many)
     */
    public function reviews()
    {
        return $this->hasMany(Review::class, 'proposal_id');
    }

    /**
     * Accessor untuk mempermudah pemanggilan format Rupiah di Blade
     * Contoh penggunaan: $proposal->biaya_rupiah
     */
    public function getBiayaRupiahAttribute()
    {
        return 'Rp ' . number_format($this->biaya, 0, ',', '.');
    }
}
