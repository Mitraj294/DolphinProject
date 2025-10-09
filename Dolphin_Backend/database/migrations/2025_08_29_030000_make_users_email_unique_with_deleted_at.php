<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        // Drop existing single-column unique index if present
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_email_unique');
            });
        } catch (\Exception $e) {
         Log::error('Error dropping unique index on users(email)', [
             'error' => $e->getMessage()
         ]);
        }

        // Add composite unique index on (email, deleted_at)
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique(['email', 'deleted_at'], 'users_email_deleted_at_unique');
            });
        } catch (\Exception $e) {
          Log::error('Error adding composite unique index on users(email, deleted_at)', [
              'error' => $e->getMessage()
          ]);
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('users_email_deleted_at_unique');
            });
        } catch (\Exception $e) {
            Log::error('Error dropping unique index on users(email, deleted_at)', [
                'error' => $e->getMessage()
            ]);
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('email', 'users_email_unique');
            });
        } catch (\Exception $e) {
            Log::error('Error adding unique index on users(email)', [
                'error' => $e->getMessage()
            ]);
        }
    }
};
