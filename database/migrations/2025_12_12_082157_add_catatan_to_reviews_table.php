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
        // CEK DULU: kalau kolom belum ada, baru ditambahkan
        if (!Schema::hasColumn('reviews', 'catatan')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->text('catatan')->nullable()->after('nilai_7');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // CEK DULU: kalau kolom ada, baru dihapus
        if (Schema::hasColumn('reviews', 'catatan')) {
            Schema::table('reviews', function (Blueprint $table) {
                $table->dropColumn('catatan');
            });
        }
    }
};
