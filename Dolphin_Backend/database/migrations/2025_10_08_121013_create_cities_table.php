<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCitiesTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cities')) {
            Schema::create('cities', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('name', 255);
                $table->mediumInteger('state_id')->unsigned();
                $table->mediumInteger('country_id')->unsigned();
                $table->timestamp('created_at')->default('2014-01-01 06:31:01');
                $table->timestamp('updated_at')->useCurrentOnUpdate();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
}
