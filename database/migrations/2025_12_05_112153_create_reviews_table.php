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
       Schema::create('reviews', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('proposal_id');
    $table->unsignedBigInteger('reviewer_id');

    $table->integer('nilai_1')->nullable();
    $table->integer('nilai_2')->nullable();
    $table->integer('nilai_3')->nullable();
    $table->integer('nilai_4')->nullable();
    $table->integer('nilai_5')->nullable();
    $table->integer('nilai_6')->nullable();
    $table->integer('nilai_7')->nullable();

    $table->integer('total_score')->nullable();
    $table->timestamps();

     // foreign key
    $table->foreign('proposal_id')->references('id')->on('proposals')->onDelete('cascade');
    $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('cascade');

});

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
