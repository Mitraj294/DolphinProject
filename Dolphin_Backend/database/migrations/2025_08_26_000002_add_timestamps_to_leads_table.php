<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimestampsToLeadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            if (!Schema::hasColumn('leads', 'assessment_sent_at')) {
                $table->timestamp('assessment_sent_at')->nullable()->after('status');
            }
            if (!Schema::hasColumn('leads', 'registered_at')) {
                $table->timestamp('registered_at')->nullable()->after('assessment_sent_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            if (Schema::hasColumn('leads', 'registered_at')) {
                $table->dropColumn('registered_at');
            }
            if (Schema::hasColumn('leads', 'assessment_sent_at')) {
                $table->dropColumn('assessment_sent_at');
            }
        });
    }
}
