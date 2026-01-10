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
            // Hapus baris file_laporan dan keterangan jika sudah ada di database

            // Cukup tambahkan yang laporan AKHIR saja
            $table->string('file_laporan_akhir')->nullable();
            $table->text('keterangan_akhir')->nullable();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn(['file_laporan_akhir', 'keterangan_akhir']);
        });
}
};
