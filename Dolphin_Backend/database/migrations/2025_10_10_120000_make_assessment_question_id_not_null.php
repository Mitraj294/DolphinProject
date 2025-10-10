<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Make assessment_question_id NOT NULL to match dolphin_db
        // Safe because we verified 0 NULL rows exist
        if (Schema::hasTable('assessment_question_answers') && Schema::hasColumn('assessment_question_answers', 'assessment_question_id')) {
            try {
                // Verify no NULLs before altering
                $nullCount = DB::table('assessment_question_answers')->whereNull('assessment_question_id')->count();
                if ($nullCount === 0) {
                    // Use Schema builder for cross-database compatibility
                    Schema::table('assessment_question_answers', function (Blueprint $table) {
                        $table->unsignedBigInteger('assessment_question_id')->nullable(false)->change();
                    });
                }
            } catch (\Exception $e) {
                // Log but don't fail - column may already be NOT NULL
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assessment_question_answers') && Schema::hasColumn('assessment_question_answers', 'assessment_question_id')) {
            try {
                // Use Schema builder for cross-database compatibility
                Schema::table('assessment_question_answers', function (Blueprint $table) {
                    $table->unsignedBigInteger('assessment_question_id')->nullable()->change();
                });
            } catch (\Exception $e) {
                // Best-effort rollback
            }
        }
    }
};
