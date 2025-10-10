<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assessment_answer_links')) {
            return;
        }

        Schema::create('assessment_answer_links', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedBigInteger('member_id');
            $table->string('token');
            $table->boolean('completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique('token', 'assessment_answer_links_token_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_answer_links');
    }
};
