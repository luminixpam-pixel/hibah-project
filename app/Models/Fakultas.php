<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Fakultas extends Model
{
    protected $table = 'fakultas';
    protected $fillable = ['nama_fakultas', 'kode_fakultas'];

    public function proposals()
    {
        return $this->hasMany(Proposal::class, 'fakultas_prodi');
    }
}
