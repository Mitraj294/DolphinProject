<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        if (! Schema::hasColumn('assessment_question_answers', 'assessment_question_id')) {
            Schema::table('assessment_question_answers', function (Blueprint $table) {
                $table->unsignedBigInteger('assessment_question_id')->after('assessment_id')->nullable();
                $table->foreign('assessment_question_id')
                    ->references('id')->on('assessment_question')
                    ->onDelete('cascade');
            });
        }
    }

    public function down() {
        Schema::table('assessment_question_answers', function (Blueprint $table) {
            if (Schema::hasColumn('assessment_question_answers', 'assessment_question_id')) {
                $table->dropForeign(['assessment_question_id']);
                $table->dropColumn('assessment_question_id');
            }
        });
    }
};
