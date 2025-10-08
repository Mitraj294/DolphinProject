<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentAnswerTokensTable extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_answer_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->string('token', 255)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->tinyInteger('used')->default(0);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_answer_tokens');
    }
}
