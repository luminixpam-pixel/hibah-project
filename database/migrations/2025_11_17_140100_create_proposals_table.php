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
    Schema::create('proposals', function (Blueprint $table) {
        $table->id();
        // Relasi ke User (Pemilik Proposal)
        $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

        $table->string('judul');
        $table->string('nama_ketua');

        // Kolom Anggota (JSON) - Wajib untuk validasi max 3 proposal
        $table->json('anggota')->nullable();

        // Kolom Biaya & Status - Wajib untuk Dashboard
        $table->decimal('biaya', 15, 2)->default(0);
        $table->string('status_pendanaan')->default('Proses');

        // Relasi ke Fakultas
        $table->unsignedBigInteger('fakultas_prodi')->nullable();

        $table->string('file_path')->nullable();
        $table->string('status')->default('Dikirim');
        $table->string('periode')->nullable();
        $table->timestamps();

        // Foreign Key ke tabel fakultas
        $table->foreign('fakultas_prodi')
              ->references('id')
              ->on('fakultas')
              ->onDelete('set null');
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposals');
    }
};
