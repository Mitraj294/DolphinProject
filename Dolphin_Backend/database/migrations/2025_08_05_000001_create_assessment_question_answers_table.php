<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up() {
        if (! Schema::hasTable('assessment_question_answers')) {
            Schema::create('assessment_question_answers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('assessment_id');
                $table->unsignedBigInteger('organization_assessment_question_id');
                $table->unsignedBigInteger('assessment_question_id');
                $table->unsignedBigInteger('member_id');
                $table->unsignedBigInteger('group_id');
                $table->text('answer');
                $table->timestamps();
                $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
                $table->foreign('organization_assessment_question_id')->references('id')->on('organization_assessment_questions')->onDelete('cascade');
                $table->foreign('assessment_question_id')->references('id')->on('assessment_question')->onDelete('cascade');
                $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
                $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
                $table->unique(['assessment_id', 'organization_assessment_question_id', 'member_id', 'group_id'], 'aqa_unique');
            });
        }
    }

    public function down() {
        Schema::dropIfExists('assessment_question_answers');
    }
};
