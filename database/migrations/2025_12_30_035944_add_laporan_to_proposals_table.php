<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {

            // Laporan lama (opsional / existing)
            $table->string('file_laporan')->nullable()->after('review_deadline');

            // Keterangan laporan
            $table->text('keterangan')->nullable()->after('file_laporan');

            // 🔥 Laporan Kemajuan
            $table->string('laporan_kemajuan')->nullable()->after('keterangan');

            // 🔥 Laporan Akhir
            $table->string('laporan_akhir')->nullable()->after('laporan_kemajuan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn([
                'file_laporan',
                'keterangan',
                'laporan_kemajuan',
                'laporan_akhir',
            ]);
        });
    }
};
