<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('jobs_backup')) {
            return;
        }

        Schema::create('jobs_backup', function (Blueprint $table) {
            // The original table used a non-auto-increment id with default 0.
            $table->unsignedBigInteger('id')->default(0);
            $table->string('queue');
            $table->longText('payload');
            $table->tinyInteger('attempts')->unsigned();
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
            // No primary key defined in original DDL; keep structure as-is.
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs_backup');
    }
};
