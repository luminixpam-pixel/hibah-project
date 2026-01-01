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
        Schema::create('dokumen_resmi', function (Blueprint $table) {
        $table->id();
        $table->string('judul');
        $table->enum('kategori', ['Panduan', 'RAB', 'Template', 'Pengumuman']);
        $table->text('deskripsi')->nullable();
        $table->string('file_path');
        $table->foreignId('uploaded_by')->constrained('users');
        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dokumen_resmis');
    }
};
