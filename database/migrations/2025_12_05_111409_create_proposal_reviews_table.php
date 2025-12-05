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
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('proposal_reviews');
    }
};
