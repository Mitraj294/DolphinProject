<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('groups') && Schema::hasColumn('groups', 'user_id')) {
            // make user_id nullable to allow creating groups without specifying a user
            Schema::table('groups', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('groups') && Schema::hasColumn('groups', 'user_id')) {
            Schema::table('groups', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
            });
        }
    }
};
