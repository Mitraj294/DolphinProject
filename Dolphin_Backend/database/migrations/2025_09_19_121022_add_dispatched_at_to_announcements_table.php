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
        if (! Schema::hasTable('announcements')) {
            return;
        }

        if (Schema::hasColumn('announcements', 'dispatched_at')) {
            return;
        }

        Schema::table('announcements', function (Blueprint $table) {
            $table->timestamp('dispatched_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('announcements', function (Blueprint $table) {
            $table->dropColumn('dispatched_at');
        });
    }
};
