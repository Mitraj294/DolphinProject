<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMiscRemainingTables extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('scheduled_emails')) {
            Schema::create('scheduled_emails', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('assessment_id')->nullable()->index();
                $table->unsignedBigInteger('group_id')->nullable()->index();
                $table->unsignedBigInteger('member_id')->nullable()->index();
                $table->string('recipient_email', 255);
                $table->string('subject', 255);
                $table->text('body');
                $table->dateTime('send_at')->nullable();
                $table->boolean('sent')->default(false);
                $table->timestamp('sent_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id', 255)->primary();
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
                $table->timestamp('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('user_details')) {
            Schema::create('user_details', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('user_id')->nullable()->index();
                $table->string('phone', 255)->nullable();
                $table->string('find_us', 255)->nullable();
                $table->string('address', 255)->nullable();
                $table->unsignedBigInteger('country_id')->nullable()->index();
                $table->unsignedBigInteger('state_id')->nullable()->index();
                $table->unsignedBigInteger('city_id')->nullable()->index();
                $table->string('zip', 255)->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email', 255)->index();
                $table->string('token', 255);
                $table->timestamp('created_at')->nullable();
            });
        }

        if (!Schema::hasTable('questions')) {
            Schema::create('questions', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('question', 255);
                $table->json('options')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
            });
        }

        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 255)->unique();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }

        if (!Schema::hasTable('user_roles')) {
            Schema::create('user_roles', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('role_id');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
                $table->primary(['user_id', 'role_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('user_details');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('scheduled_emails');
    }
}
