<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('proposals', function (Blueprint $table) {
        $table->id();
        $table->string('nama_ketua');
        $table->text('anggota')->nullable();
        $table->string('biaya')->nullable();
        $table->string('judul')->nullable();
        $table->string('file_path')->nullable();
        $table->timestamps();
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
