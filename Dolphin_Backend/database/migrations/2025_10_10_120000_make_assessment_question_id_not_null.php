<?php

use Illuminate\Database\Migrations\Migration;
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
                    DB::statement('ALTER TABLE `assessment_question_answers` MODIFY `assessment_question_id` bigint unsigned NOT NULL');
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
                DB::statement('ALTER TABLE `assessment_question_answers` MODIFY `assessment_question_id` bigint unsigned NULL');
            } catch (\Exception $e) {
                // Best-effort rollback
            }
        }
    }
};
