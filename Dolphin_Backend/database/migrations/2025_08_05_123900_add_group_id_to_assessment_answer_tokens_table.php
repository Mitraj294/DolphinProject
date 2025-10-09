<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('assessment_answer_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('group_id')->nullable()->after('member_id');
        });
    }

    public function down()
    {
        Schema::table('assessment_answer_tokens', function (Blueprint $table) {
            $table->dropColumn('group_id');
        });
    }
};
