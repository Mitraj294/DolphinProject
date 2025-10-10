<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (! Schema::hasTable('assessment_question')) {
            if (! Schema::hasTable('assessment_question')) {
                Schema::create('assessment_question', function (Blueprint $table) {
                    $table->id();
                    $table->unsignedBigInteger('assessment_id');
                    $table->unsignedBigInteger('question_id'); // organization_assessment_questions.id
                    $table->timestamps();
                    $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
                    $table->foreign('question_id')->references('id')->on('organization_assessment_questions')->onDelete('cascade');
                    $table->unique(['assessment_id', 'question_id']);
                });
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('assessment_question');
    }
};
