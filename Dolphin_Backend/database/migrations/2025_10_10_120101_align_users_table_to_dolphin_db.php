<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // This migration is MySQL-specific for aligning with local dolphin_db
        // PostgreSQL doesn't need these changes as it was created from scratch
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'mysql') {
            return; // Skip for non-MySQL databases
        }

        if (!Schema::hasTable('users')) {
            return;
        }

        // Step 1: Drop the 'role' column if it exists
        if (Schema::hasColumn('users', 'role')) {
            try {
                DB::statement('ALTER TABLE `users` DROP COLUMN `role`');
            } catch (\Exception $e) {
                // Column may not exist or other error
            }
        }

        // Step 2: Convert timestamp columns to datetime to match dolphin_db
        // We'll do this by creating new datetime columns, copying data, dropping old, and renaming

        // Convert created_at from TIMESTAMP to DATETIME
        if (Schema::hasColumn('users', 'created_at')) {
            try {
                // Check if it's currently timestamp
                $columnType = DB::select("SELECT DATA_TYPE FROM information_schema.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'users' 
                    AND COLUMN_NAME = 'created_at'");
                
                if (!empty($columnType) && strtolower($columnType[0]->DATA_TYPE) === 'timestamp') {
                    // Create temp column, copy data, drop old, rename
                    DB::statement('ALTER TABLE `users` ADD COLUMN `created_at_temp` DATETIME NULL');
                    DB::statement('UPDATE `users` SET `created_at_temp` = `created_at`');
                    DB::statement('ALTER TABLE `users` DROP COLUMN `created_at`');
                    DB::statement('ALTER TABLE `users` CHANGE COLUMN `created_at_temp` `created_at` DATETIME NULL');
                }
            } catch (\Exception $e) {
                // Best effort
            }
        }

        // Convert updated_at from TIMESTAMP to DATETIME
        if (Schema::hasColumn('users', 'updated_at')) {
            try {
                $columnType = DB::select("SELECT DATA_TYPE FROM information_schema.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'users' 
                    AND COLUMN_NAME = 'updated_at'");
                
                if (!empty($columnType) && strtolower($columnType[0]->DATA_TYPE) === 'timestamp') {
                    DB::statement('ALTER TABLE `users` ADD COLUMN `updated_at_temp` DATETIME NULL');
                    DB::statement('UPDATE `users` SET `updated_at_temp` = `updated_at`');
                    DB::statement('ALTER TABLE `users` DROP COLUMN `updated_at`');
                    DB::statement('ALTER TABLE `users` CHANGE COLUMN `updated_at_temp` `updated_at` DATETIME NULL');
                }
            } catch (\Exception $e) {
                // Best effort
            }
        }

        // Convert deleted_at from TIMESTAMP to DATETIME
        if (Schema::hasColumn('users', 'deleted_at')) {
            try {
                $columnType = DB::select("SELECT DATA_TYPE FROM information_schema.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'users' 
                    AND COLUMN_NAME = 'deleted_at'");
                
                if (!empty($columnType) && strtolower($columnType[0]->DATA_TYPE) === 'timestamp') {
                    DB::statement('ALTER TABLE `users` ADD COLUMN `deleted_at_temp` DATETIME NULL');
                    DB::statement('UPDATE `users` SET `deleted_at_temp` = `deleted_at`');
                    DB::statement('ALTER TABLE `users` DROP COLUMN `deleted_at`');
                    DB::statement('ALTER TABLE `users` CHANGE COLUMN `deleted_at_temp` `deleted_at` DATETIME NULL');
                }
            } catch (\Exception $e) {
                // Best effort
            }
        }
    }

    public function down(): void
    {
        // This migration is MySQL-specific
        $driver = DB::connection()->getDriverName();
        if ($driver !== 'mysql') {
            return; // Skip for non-MySQL databases
        }

        if (!Schema::hasTable('users')) {
            return;
        }

        // Reverse: Add role column back
        if (!Schema::hasColumn('users', 'role')) {
            try {
                DB::statement("ALTER TABLE `users` ADD COLUMN `role` VARCHAR(255) NOT NULL DEFAULT 'user' AFTER `password`");
            } catch (\Exception $e) {
                // Best effort
            }
        }

        // Convert datetime columns back to timestamp
        // (Note: This may lose some precision, but it's a best-effort rollback)
        
        if (Schema::hasColumn('users', 'created_at')) {
            try {
                $columnType = DB::select("SELECT DATA_TYPE FROM information_schema.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'users' 
                    AND COLUMN_NAME = 'created_at'");
                
                if (!empty($columnType) && strtolower($columnType[0]->DATA_TYPE) === 'datetime') {
                    DB::statement('ALTER TABLE `users` MODIFY COLUMN `created_at` TIMESTAMP NULL');
                }
            } catch (\Exception $e) {
                // Best effort
            }
        }

        if (Schema::hasColumn('users', 'updated_at')) {
            try {
                $columnType = DB::select("SELECT DATA_TYPE FROM information_schema.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'users' 
                    AND COLUMN_NAME = 'updated_at'");
                
                if (!empty($columnType) && strtolower($columnType[0]->DATA_TYPE) === 'datetime') {
                    DB::statement('ALTER TABLE `users` MODIFY COLUMN `updated_at` TIMESTAMP NULL');
                }
            } catch (\Exception $e) {
                // Best effort
            }
        }

        if (Schema::hasColumn('users', 'deleted_at')) {
            try {
                $columnType = DB::select("SELECT DATA_TYPE FROM information_schema.COLUMNS 
                    WHERE TABLE_SCHEMA = DATABASE() 
                    AND TABLE_NAME = 'users' 
                    AND COLUMN_NAME = 'deleted_at'");
                
                if (!empty($columnType) && strtolower($columnType[0]->DATA_TYPE) === 'datetime') {
                    DB::statement('ALTER TABLE `users` MODIFY COLUMN `deleted_at` TIMESTAMP NULL');
                }
            } catch (\Exception $e) {
                // Best effort
            }
        }
    }
};
