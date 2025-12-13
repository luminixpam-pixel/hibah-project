<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up()
{
    Schema::create('proposal_reviewers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('proposal_id')->constrained()->onDelete('cascade');
        $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
        $table->timestamps();

        $table->unique(['proposal_id', 'reviewer_id']);
    });
}

public function down()
{
    Schema::dropIfExists('proposal_reviewers');
}

};
