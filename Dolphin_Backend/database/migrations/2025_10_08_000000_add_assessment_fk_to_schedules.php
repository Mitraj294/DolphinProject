<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add foreign key only if assessments table exists and schedules table exists
        if (Schema::hasTable('assessments') && Schema::hasTable('assessment_schedules')) {
            Schema::table('assessment_schedules', function (Blueprint $table) {
                // Prevent adding same FK twice
                if (! Schema::hasColumn('assessment_schedules', 'assessment_id')) {
                    return;
                }

                $sm = Schema::getConnection()->getDoctrineSchemaManager();
                $indexes = @array_column($sm->listTableForeignKeys('assessment_schedules'), 'name');

                // Add FK if not present
                $exists = false;
                foreach ($sm->listTableForeignKeys('assessment_schedules') as $fk) {
                    if (in_array('assessment_schedules_assessment_id_foreign', (array) $fk->getName())) {
                        $exists = true;
                        break;
                    }
                }

                if (! $exists) {
                    $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assessment_schedules')) {
            Schema::table('assessment_schedules', function (Blueprint $table) {
                $table->dropForeign(['assessment_id']);
            });
        }
    }
};
