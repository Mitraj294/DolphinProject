<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobsBackupTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('jobs_backup')) {
            Schema::create('jobs_backup', function (Blueprint $table) {
                $table->bigInteger('id')->default(0);
                $table->string('queue', 255);
                $table->longText('payload');
                $table->tinyInteger('attempts')->unsigned();
                $table->unsignedInteger('reserved_at')->nullable();
                $table->unsignedInteger('available_at');
                $table->unsignedInteger('created_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('jobs_backup');
    }
}
