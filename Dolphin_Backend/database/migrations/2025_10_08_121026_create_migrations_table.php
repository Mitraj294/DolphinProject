<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMigrationsTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('migrations')) {
            Schema::create('migrations', function (Blueprint $table) {
                $table->increments('id');
                $table->string('migration', 255);
                $table->integer('batch');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('migrations');
    }
}
