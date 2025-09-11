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
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name');
            // prefer explicit organization_size column naming for consistency with user_details
            $table->string('organization_size')->nullable();
            $table->string('source')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->nullable();
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
            $table->string('main_contact')->nullable();
            $table->string('admin_email')->nullable();
            $table->string('admin_phone')->nullable();
            $table->string('sales_person')->nullable();
            $table->date('last_contacted')->nullable();
            $table->integer('certified_staff')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('organizations');
    }
};
