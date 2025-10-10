<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('first_name');
                $table->string('last_name')->nullable();
                $table->string('email')->unique();
                $table->string('password');
                // role was present in some environments; keep schema aligned with dolphin_db (no role column)
                $table->string('phone')->nullable();

                // Use DATETIME columns for created_at/updated_at to match dolphin_db
                $table->dateTime('created_at')->nullable();
                $table->dateTime('updated_at')->nullable();

                // soft delete as DATETIME to match existing dolphin_db representation
                $table->dateTime('deleted_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
}