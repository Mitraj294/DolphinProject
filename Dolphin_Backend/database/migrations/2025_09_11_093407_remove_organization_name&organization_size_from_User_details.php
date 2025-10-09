<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
    
        Schema::table('user_details', function (Blueprint $table) {
            if (Schema::hasColumn('user_details', 'organization_name')) {
                $table->dropColumn('organization_name');
            }
            if (Schema::hasColumn('user_details', 'organization_size')) {
                $table->dropColumn('organization_size');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the columns if they do not exist. Positioning is best-effort on MySQL.
        Schema::table('user_details', function (Blueprint $table) {
            if (!Schema::hasColumn('user_details', 'organization_name')) {
                $table->string('organization_name')->nullable()->after('find_us');
            }
            if (!Schema::hasColumn('user_details', 'organization_size')) {
                $table->string('organization_size')->nullable()->after('organization_name');
            }
        });

        // Attempt to reposition columns on MySQL using raw statements; don't fail on error
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            try {
                if (Schema::hasColumn('user_details', 'organization_name')) {
                    DB::statement('ALTER TABLE `user_details` MODIFY COLUMN `organization_name` VARCHAR(255) NULL AFTER `find_us`');
                }
                if (Schema::hasColumn('user_details', 'organization_size')) {
                    DB::statement('ALTER TABLE `user_details` MODIFY COLUMN `organization_size` VARCHAR(255) NULL AFTER `organization_name`');
                }
            } catch (\Exception $e) {
                Log::warning('[migration] could not reposition organization_name/organization_size in down()', ['error' => $e->getMessage()]);
            }
        }
    }
};
