<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('assessment_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->date('date');
            $table->time('time');
            $table->json('group_ids')->nullable();
            $table->json('member_ids')->nullable();
            $table->timestamps();
            // Intentionally not adding FK here to avoid migration ordering issues.
            // A separate migration will add the foreign key once 'assessments' exists.
        });
    }

    public function down()
    {
        Schema::dropIfExists('assessment_schedules');
    }
};
