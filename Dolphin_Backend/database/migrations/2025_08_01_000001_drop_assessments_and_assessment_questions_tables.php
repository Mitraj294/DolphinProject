<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('assessment_questions');
        Schema::dropIfExists('assessments');
    }

    public function down(): void
    {
        // No-op: tables are not recreated
    }
};
