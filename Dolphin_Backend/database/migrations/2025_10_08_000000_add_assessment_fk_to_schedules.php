<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add foreign key only if assessments table exists and schedules table exists
        if (!Schema::hasTable('assessments') || !Schema::hasTable('assessment_schedules')) {
            return;
        }

        if (!Schema::hasColumn('assessment_schedules', 'assessment_id')) {
            return;
        }

        // Check if FK already exists using cross-database compatible query
        try {
            $driver = DB::connection()->getDriverName();
            $fkName = 'assessment_schedules_assessment_id_foreign';
            
            if ($driver === 'mysql') {
                $row = DB::selectOne(
                    "SELECT COUNT(*) AS cnt FROM information_schema.table_constraints 
                    WHERE constraint_type='FOREIGN KEY' 
                    AND constraint_name = ? 
                    AND table_schema = DATABASE()",
                    [$fkName]
                );
                if ($row && ($row->cnt ?? 0) > 0) {
                    return;
                }
            } elseif ($driver === 'pgsql') {
                $row = DB::selectOne(
                    "SELECT COUNT(*) AS cnt FROM information_schema.table_constraints 
                    WHERE constraint_type='FOREIGN KEY' 
                    AND constraint_name = ? 
                    AND table_schema = current_schema()",
                    [$fkName]
                );
                if ($row && ($row->cnt ?? 0) > 0) {
                    return;
                }
            }
        } catch (\Throwable $e) {
            // If check fails, proceed with try/catch on FK creation
        }

        // Add foreign key with error handling
        try {
            Schema::table('assessment_schedules', function (Blueprint $table) {
                $table->foreign('assessment_id')
                    ->references('id')
                    ->on('assessments')
                    ->onDelete('cascade');
            });
        } catch (\Throwable $e) {
            // FK may already exist or other issue - safely ignore
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assessment_schedules')) {
            Schema::table('assessment_schedules', function (Blueprint $table) {
                if (Schema::hasColumn('assessment_schedules', 'assessment_id')) {
                    try {
                        $table->dropForeign(['assessment_id']);
                    } catch (\Exception $e) {
                        // FK may not exist; ignore
                    }
                }
            });
        }
    }
};
