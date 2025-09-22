<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::dropIfExists('assessment_schedules');
        Schema::dropIfExists('scheduled_emails');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not implemented to keep the refactoring simple.
        // The old tables can be recreated from older migrations if needed.
    }
};
