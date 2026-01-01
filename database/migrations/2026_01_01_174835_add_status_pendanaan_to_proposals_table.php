<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  public function up(): void
{
    Schema::table('proposals', function (Blueprint $table) {
        // Menambahkan kolom status_pendanaan setelah kolom status
        $table->string('status_pendanaan')->nullable()->after('status');
    });
}

public function down(): void
{
    Schema::table('proposals', function (Blueprint $table) {
        $table->dropColumn('status_pendanaan');
    });
}
};
