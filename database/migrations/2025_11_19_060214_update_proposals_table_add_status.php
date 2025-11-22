<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('proposals', function (Blueprint $table) {
            // tambahkan kolom baru kalau diperlukan
            if (!Schema::hasColumn('proposals', 'pengusul')) {
                $table->string('pengusul')->nullable();
            }

            if (!Schema::hasColumn('proposals', 'reviewer')) {
                $table->string('reviewer')->nullable();
            }

            if (!Schema::hasColumn('proposals', 'status')) {
                $table->string('status')->default('dikirim');
            }
        });
    }

    public function down()
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropColumn(['pengusul', 'reviewer', 'status']);
        });
    }
};
