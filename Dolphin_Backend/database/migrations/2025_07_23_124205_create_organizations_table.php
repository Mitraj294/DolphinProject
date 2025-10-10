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
        if (! Schema::hasTable('organizations')) {
            Schema::create('organizations', function (Blueprint $table) {
                $table->id();
                $table->string('organization_name');
                // prefer explicit organization_size column naming for consistency with user_details
                $table->string('organization_size')->nullable();
                // keep core organization columns only (match dolphin_db canonical schema)
                $table->date('contract_start')->nullable();
                $table->date('contract_end')->nullable();
                $table->date('last_contacted')->nullable();
                $table->integer('certified_staff')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->timestamps();
                $table->softDeletes();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
