<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // Assessments table
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();
        });

       
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('text');
            $table->json('options')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

     
   Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('question');
            $table->text('answer'); // store as JSON array
            $table->timestamps();
            $table->softDeletes();
        });
    }



    public function down(): void
    {
        Schema::dropIfExists('answers');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('assessments');
    }
};

