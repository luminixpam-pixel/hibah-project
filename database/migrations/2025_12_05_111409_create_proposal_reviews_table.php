<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tabel proposal_reviews
        Schema::create('proposal_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('proposal_id');
            $table->unsignedBigInteger('reviewer_id');

            // nilai per komponen
            $table->integer('nilai_1')->nullable();
            $table->integer('nilai_2')->nullable();
            $table->integer('nilai_3')->nullable();
            $table->integer('nilai_4')->nullable();
            $table->integer('nilai_5')->nullable();
            $table->integer('nilai_6')->nullable();
            $table->integer('nilai_7')->nullable();

            // total akhir
            $table->integer('total_score')->nullable();

            $table->timestamps();
        });

        // Tambah kolom review_deadline di tabel proposals
        Schema::table('proposals', function (Blueprint $table) {
            $table->dateTime('review_deadline')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        // Drop tabel proposal_reviews
        Schema::dropIfExists('proposal_reviews');

        // Hapus kolom review_deadline dari tabel proposals
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn('review_deadline');
        });
    }
};
