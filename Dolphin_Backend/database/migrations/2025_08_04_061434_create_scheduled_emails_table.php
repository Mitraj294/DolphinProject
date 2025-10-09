<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('scheduled_emails')) {
            Schema::create('scheduled_emails', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('assessment_id')->nullable()->index();
                $table->unsignedBigInteger('group_id')->nullable();
                $table->unsignedBigInteger('member_id')->nullable();
                $table->string('recipient_email');
                $table->string('subject');
                $table->text('body');
                $table->dateTime('send_at');
                $table->boolean('sent')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_emails');
    }
};
