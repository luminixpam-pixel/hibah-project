<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {

            // tambah kolom reviewer_id
            if (!Schema::hasColumn('reviews', 'reviewer_id')) {
                $table->foreignId('reviewer_id')
                    ->nullable()
                    ->after('proposal_id')
                    ->constrained('users')
                    ->nullOnDelete();
            }

            // 1 reviewer hanya boleh 1 review per proposal
            $table->unique(
                ['proposal_id', 'reviewer_id'],
                'reviews_unique_proposal_reviewer'
            );
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {

            // hapus unique index
            $table->dropUnique('reviews_unique_proposal_reviewer');

            // hapus foreign key & kolom
            if (Schema::hasColumn('reviews', 'reviewer_id')) {
                $table->dropConstrainedForeignId('reviewer_id');
            }
        });
    }
};
