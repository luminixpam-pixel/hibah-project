<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::rename('notifications', 'review_selesai');
    }

    public function down(): void
    {
        Schema::rename('review_selesai', 'notifications');
    }
};
