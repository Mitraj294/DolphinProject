<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('members') && Schema::hasColumn('members', 'user_id')) {
            // make user_id nullable to allow creating members without specifying a linked user
            Schema::table('members', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('members') && Schema::hasColumn('members', 'user_id')) {
            Schema::table('members', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable(false)->change();
            });
        }
    }
};
