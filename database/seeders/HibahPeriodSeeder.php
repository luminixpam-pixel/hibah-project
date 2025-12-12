<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HibahPeriodSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('hibah_periods')->insert([
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
