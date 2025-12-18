<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Notification;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('proposal_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title');
            $table->string('type')->default('info'); // info, success, warning
            $table->boolean('is_read')->default(false);
            $table->text('message')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_read']);
});

    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
