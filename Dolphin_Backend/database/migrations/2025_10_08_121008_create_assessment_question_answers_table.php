<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentQuestionAnswersTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('assessment_question_answers')) {
            Schema::create('assessment_question_answers', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('assessment_id');
                $table->unsignedBigInteger('organization_assessment_question_id');
                $table->unsignedBigInteger('assessment_question_id');
                $table->unsignedBigInteger('member_id');
                $table->unsignedBigInteger('group_id')->nullable();
                $table->text('answer');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_question_answers');
    }
}
