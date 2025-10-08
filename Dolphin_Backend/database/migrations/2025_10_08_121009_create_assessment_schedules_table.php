<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAssessmentSchedulesTable extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('assessment_id');
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->json('group_ids')->nullable();
            $table->json('member_ids')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_schedules');
    }
}
