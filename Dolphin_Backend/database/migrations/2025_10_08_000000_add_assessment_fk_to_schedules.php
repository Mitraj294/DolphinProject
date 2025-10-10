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
        if (Schema::hasTable('assessments') && Schema::hasTable('assessment_schedules')) {

            // If a constraint with this name already exists in the database, skip creating it.
            try {
                $fkName = 'assessment_schedules_assessment_id_foreign';
                $row = DB::selectOne("SELECT COUNT(*) AS cnt FROM information_schema.table_constraints WHERE constraint_type='FOREIGN KEY' AND constraint_name = ? AND table_schema = DATABASE()", [$fkName]);
                if ($row && ($row->cnt ?? $row->CNT ?? 0) > 0) {
                    return;
                }
            } catch (\Throwable $e) {
                // information_schema query failed (non-MySQL or permission issues). Fall back to best-effort creation below.
            }

            Schema::table('assessment_schedules', function (Blueprint $table) {
                // Prevent adding same FK twice
                if (! Schema::hasColumn('assessment_schedules', 'assessment_id')) {
                    return;
                }

                // Try to detect existing foreign keys using Doctrine if available.
                $sm = null;
                try {
                    $sm = Schema::getConnection()->getDoctrineSchemaManager();
                } catch (\Throwable $e) {
                    // Doctrine DBAL / schema manager not available in this environment.
                    $sm = null;
                }

                $exists = false;
                if ($sm) {
                    try {
                        foreach ($sm->listTableForeignKeys('assessment_schedules') as $fk) {
                            if ($fk->getName() === 'assessment_schedules_assessment_id_foreign') {
                                $exists = true;
                                break;
                            }
                        }
                    } catch (\Throwable $e) {
                        // ignore and fall back to guarded create
                        $exists = false;
                    }
                }

                if (! $exists) {
                    try {
                        $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
                    } catch (\Throwable $e) {
                        // Could not create FK (missing doctrine, insufficient privileges, or it already exists). Ignore.
                    }
                }
            });
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
