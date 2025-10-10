<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('members')) {
            return;
        }

        // Drop existing single-column unique index if present, then create composite unique index
        // Use a portable DB query to check for index existence instead of Doctrine.
        $conn = Schema::getConnection();
        $driver = $conn->getDriverName();

        // check for common index name
        $hasSingleIndex = false;
        try {
            if ($driver === 'mysql') {
                $res = DB::select("SHOW INDEX FROM `members` WHERE Key_name = 'members_email_unique'");
                $hasSingleIndex = !empty($res);
            } else {
                // Fallback: attempt to drop and ignore errors
                $hasSingleIndex = false;
            }
        } catch (\Exception $e) {
            $hasSingleIndex = false;
        }

        if ($hasSingleIndex) {
            Schema::table('members', function (Blueprint $table) {
                $table->dropUnique('members_email_unique');
            });
        }

        Schema::table('members', function (Blueprint $table) {
            // Add composite unique index on (email, deleted_at) if not already present
            // Use a safe name 'members_email_deleted_at_unique'
            $conn = Schema::getConnection();
            $driver = $conn->getDriverName();
            $hasComposite = false;
            try {
                if ($driver === 'mysql') {
                    $res = DB::select("SHOW INDEX FROM `members` WHERE Key_name = 'members_email_deleted_at_unique'");
                    $hasComposite = !empty($res);
                }
            } catch (\Exception $e) {
                $hasComposite = false;
            }

            if (! $hasComposite) {
                $table->unique(['email', 'deleted_at'], 'members_email_deleted_at_unique');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('members')) {
            return;
        }

        // Drop composite unique index if exists, then recreate single-column unique index
        try {
            Schema::table('members', function (Blueprint $table) {
                $table->dropUnique('members_email_deleted_at_unique');
            });
        } catch (\Exception $e) {
         Log::error('Error dropping unique index on members(email, deleted_at)', [
             'error' => $e->getMessage()
         ]);
        }

        try {
            Schema::table('members', function (Blueprint $table) {
                $table->unique('email', 'members_email_unique');
            });
        } catch (\Exception $e) {
            Log::error('Error adding unique index on members(email)', [
                'error' => $e->getMessage()
            ]);
        }
    }
};
