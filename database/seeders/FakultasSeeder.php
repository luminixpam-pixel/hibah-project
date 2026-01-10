<?php

namespace Database\Seeders;

use App\Models\Fakultas;
use Illuminate\Database\Seeder;

class FakultasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ['nama_fakultas' => 'Fakultas Kedokteran', 'kode_fakultas' => 'FK'],
            ['nama_fakultas' => 'Fakultas Kedokteran Gigi', 'kode_fakultas' => 'FKG'],
            ['nama_fakultas' => 'Fakultas Teknologi Informasi', 'kode_fakultas' => 'FTI'],
            ['nama_fakultas' => 'Fakultas Ekonomi dan Bisnis', 'kode_fakultas' => 'FEB'],
            ['nama_fakultas' => 'Fakultas Hukum', 'kode_fakultas' => 'FH'],
            ['nama_fakultas' => 'Fakultas Psikologi', 'kode_fakultas' => 'PSI'],
        ];

        foreach ($data as $item) {
            Fakultas::updateOrCreate(
                ['kode_fakultas' => $item['kode_fakultas']],
                ['nama_fakultas' => $item['nama_fakultas']]
            );
        }
    }
}
