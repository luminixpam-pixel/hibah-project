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
    Schema::table('users', function (Blueprint $table) {
        $table->string('nidn')->nullable();
        $table->string('phone')->nullable();
        $table->string('fakultas')->nullable();
        $table->string('prodi')->nullable();
        $table->string('jabatan')->nullable();
        $table->string('role')->default('User');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn(['nidn', 'phone', 'fakultas', 'prodi', 'jabatan', 'role']);
    });
}
};
