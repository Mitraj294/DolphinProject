<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Questions table
        if (! Schema::hasTable('questions')) {
            Schema::create('questions', function (Blueprint $table) {
                $table->id();
                $table->string('question');
                $table->json('options')->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }

        if (! Schema::hasTable('answers')) {
            Schema::create('answers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->string('question');
                $table->text('answer');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }



    public function down(): void
    {
        Schema::dropIfExists('answers');
        Schema::dropIfExists('questions');
    }
};

