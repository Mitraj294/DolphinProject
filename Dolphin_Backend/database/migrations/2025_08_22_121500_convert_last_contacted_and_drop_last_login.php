<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Drop last_login if it was added earlier
            if (Schema::hasColumn('organizations', 'last_login')) {
                $table->dropColumn('last_login');
            }
        });

        // Change last_contacted to datetime so it can store date+time
        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'last_contacted')) {
                try {
                    $table->dateTime('last_contacted')->nullable()->change();
                } catch (\Exception $e) {
                    // Some DB drivers (SQLite) don't support change(); ignore when running tests/dev where not needed
                }
            } else {
                $table->dateTime('last_contacted')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            // Recreate last_login as nullable datetime
            if (!Schema::hasColumn('organizations', 'last_login')) {
                $table->dateTime('last_login')->nullable();
            }
        });

        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'last_contacted')) {
                try {
                    $table->date('last_contacted')->nullable()->change();
                } catch (\Exception $e) {
                    Log::error('Error changing last_contacted to date', [
                        'error' => $e->getMessage()
                    ]);
                }
            }
        });
    }
};
