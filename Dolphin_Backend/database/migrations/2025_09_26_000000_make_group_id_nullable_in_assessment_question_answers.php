<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Check if table and column exist before modifying
        if (!Schema::hasTable('assessment_question_answers')) {
            return;
        }

        if (!Schema::hasColumn('assessment_question_answers', 'group_id')) {
            return;
        }

        // Use Laravel's Schema builder for cross-database compatibility
        Schema::table('assessment_question_answers', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->change();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('assessment_question_answers')) {
            return;
        }

        if (!Schema::hasColumn('assessment_question_answers', 'group_id')) {
            return;
        }

        // Revert to NOT NULL. Be careful: this will fail if NULLs exist.
        Schema::table('assessment_question_answers', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable(false)->change();
        });
    }
};
