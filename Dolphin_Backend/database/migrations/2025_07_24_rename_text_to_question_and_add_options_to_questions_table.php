<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'text')) {
                $table->renameColumn('text', 'question');
            }
            if (!Schema::hasColumn('questions', 'options')) {
                $table->json('options')->nullable()->after('question');
            }
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            if (Schema::hasColumn('questions', 'question')) {
                $table->renameColumn('question', 'text');
            }
            if (Schema::hasColumn('questions', 'options')) {
                $table->dropColumn('options');
            }
        });
    }
};
