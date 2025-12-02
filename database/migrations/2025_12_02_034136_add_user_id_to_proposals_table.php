<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('proposals', function (Blueprint $table) {
            if (!Schema::hasColumn('proposals', 'user_id')) {
                $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            }
        });
    }

    public function down()
    {
        Schema::table('proposals', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
