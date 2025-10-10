<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assessments')) {
            Schema::create('assessments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->nullable();
                $table->unsignedBigInteger('organization_id')->nullable();
                $table->string('name');
                // add foreign keys if users/organizations tables exist at runtime
                $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_question');
        Schema::dropIfExists('assessments');
    }
};
