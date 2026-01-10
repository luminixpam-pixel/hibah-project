<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('proposals', function (Blueprint $table) {

            if (!Schema::hasColumn('proposals', 'biaya')) {
                $table->decimal('biaya', 15, 2)->default(0);
            }

            if (!Schema::hasColumn('proposals', 'status_pendanaan')) {
                $table->string('status_pendanaan')->default('Proses');
            }

        });
    }

    public function down(): void
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn(['biaya', 'status_pendanaan']);
        });
    }
};
