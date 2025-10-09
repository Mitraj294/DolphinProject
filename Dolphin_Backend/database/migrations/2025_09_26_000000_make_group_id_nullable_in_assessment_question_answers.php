<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Use raw SQL to avoid requiring doctrine/dbal for column modification.
        DB::statement('ALTER TABLE `assessment_question_answers` MODIFY `group_id` bigint unsigned NULL');
    }

    public function down(): void
    {
        // Revert to NOT NULL. Be careful: this will fail if NULLs exist.
        DB::statement('ALTER TABLE `assessment_question_answers` MODIFY `group_id` bigint unsigned NOT NULL');
    }
};
