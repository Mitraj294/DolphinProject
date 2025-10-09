<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('members')) {
            Schema::create('members', function (Blueprint $table) {
                $table->id();
                $table->foreignId('organization_id')->nullable()->constrained('organizations')->onDelete('cascade');
                $table->foreignId('user_id')->nullable();
                $table->string('first_name');
                $table->string('last_name');
                $table->string('email');
                $table->string('phone')->nullable();
                $table->timestamps();
                $table->softDeletes();

                $table->unique(['email', 'deleted_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
