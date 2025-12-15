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

            if (!Schema::hasColumn('users', 'nidn')) {
                $table->string('nidn')->nullable();
            }

            if (!Schema::hasColumn('users', 'phone')) {
                $table->string('phone')->nullable();
            }

            if (!Schema::hasColumn('users', 'fakultas')) {
                $table->string('fakultas')->nullable();
            }

            if (!Schema::hasColumn('users', 'prodi')) {
                $table->string('prodi')->nullable();
            }

            if (!Schema::hasColumn('users', 'jabatan')) {
                $table->string('jabatan')->nullable();
            }

            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->default('User');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {

            if (Schema::hasColumn('users', 'nidn')) {
                $table->dropColumn('nidn');
            }

            if (Schema::hasColumn('users', 'phone')) {
                $table->dropColumn('phone');
            }

            if (Schema::hasColumn('users', 'fakultas')) {
                $table->dropColumn('fakultas');
            }

            if (Schema::hasColumn('users', 'prodi')) {
                $table->dropColumn('prodi');
            }

            if (Schema::hasColumn('users', 'jabatan')) {
                $table->dropColumn('jabatan');
            }

            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
        });
    }
};
