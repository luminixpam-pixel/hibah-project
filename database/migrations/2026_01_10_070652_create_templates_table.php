<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up()
{
    Schema::create('templates', function (Blueprint $table) {
        $table->id();
        $table->string('nama_template'); // Contoh: Template Laporan Kemajuan
        $table->string('file_path');
        $table->string('jenis'); // Contoh: laporan_kemajuan, proposal, dsb.
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('templates');
    }
};
