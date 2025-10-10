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
        if (! Schema::hasTable('user_details')) {
            Schema::create('user_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
                $table->string('first_name')->nullable();
                $table->string('last_name')->nullable();
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('find_us')->nullable();
                $table->string('organization_name')->nullable();
                $table->string('organization_size')->nullable();
                $table->string('address')->nullable();
                $table->unsignedBigInteger('country_id')->nullable()->after('address');
                $table->unsignedBigInteger('state_id')->nullable()->after('country_id');
                $table->unsignedBigInteger('city_id')->nullable()->after('state_id');
                // foreign keys will be added if referenced tables exist at runtime
                $table->string('zip')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_details');
    }
};
