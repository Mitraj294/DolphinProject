<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatesTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('states')) {
            Schema::create('states', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 255);
                $table->mediumInteger('country_id')->unsigned();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->useCurrentOnUpdate();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('states');
    }
}
