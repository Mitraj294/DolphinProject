<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            // ignore
        }

        // Add composite unique index on (email, deleted_at)
        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique(['email', 'deleted_at'], 'users_email_deleted_at_unique');
            });
        } catch (\Exception $e) {
            // ignore
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
        }

        try {
            Schema::table('users', function (Blueprint $table) {
                $table->unique('email', 'users_email_unique');
            });
        } catch (\Exception $e) {
        }
    }
};
