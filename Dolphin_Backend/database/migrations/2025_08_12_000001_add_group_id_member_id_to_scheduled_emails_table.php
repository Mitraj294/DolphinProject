<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        Schema::table('scheduled_emails', function (Blueprint $table) {
            if (!Schema::hasColumn('scheduled_emails', 'group_id')) {
                $table->unsignedBigInteger('group_id')->nullable()->after('assessment_id');
            }
            if (!Schema::hasColumn('scheduled_emails', 'member_id')) {
                $table->unsignedBigInteger('member_id')->nullable()->after('group_id');
            }
        });
    }
    public function down() {
        Schema::table('scheduled_emails', function (Blueprint $table) {
            if (Schema::hasColumn('scheduled_emails', 'group_id')) {
                $table->dropColumn('group_id');
            }
            if (Schema::hasColumn('scheduled_emails', 'member_id')) {
                $table->dropColumn('member_id');
            }
        });
    }
};
