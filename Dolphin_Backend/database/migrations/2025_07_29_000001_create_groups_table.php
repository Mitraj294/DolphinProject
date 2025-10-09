<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('groups')) {
            Schema::create('groups', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->nullable()->constrained('organizations')->onDelete('cascade');
                $table->foreignId('user_id')->nullable();
                $table->string('name');
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('groups');
    }
};
