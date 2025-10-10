<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('leads')) {
            Schema::create('leads', function (Blueprint $table) {
                $table->id();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email');
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
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
