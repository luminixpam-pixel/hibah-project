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
            // Menambahkan kolom untuk path file laporan dan teks keterangan
            // 'nullable' agar tidak error jika data lama belum memiliki laporan
            $table->string('file_laporan')->nullable()->after('review_deadline');
            $table->text('keterangan')->nullable()->after('file_laporan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            // Menghapus kolom jika migrasi di-rollback
            $table->dropColumn(['file_laporan', 'keterangan']);
        });
    }
};
