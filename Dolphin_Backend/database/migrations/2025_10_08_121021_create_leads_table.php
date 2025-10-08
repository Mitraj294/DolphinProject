<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLeadsTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('leads')) {
            Schema::create('leads', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('created_by')->nullable();
                $table->string('first_name', 255);
                $table->string('last_name', 255);
                $table->string('email', 255);
                $table->string('phone', 255)->nullable();
                $table->string('find_us', 255)->nullable();
                $table->string('organization_name', 255)->nullable();
                $table->string('organization_size', 255)->nullable();
                $table->string('address', 255)->nullable();
                $table->unsignedBigInteger('country_id')->nullable();
                $table->unsignedBigInteger('state_id')->nullable();
                $table->unsignedBigInteger('city_id')->nullable();
                $table->string('zip', 255)->nullable();
                $table->text('notes')->nullable();
                $table->string('status')->default('Lead Stage');
                $table->timestamp('assessment_sent_at')->nullable();
                $table->timestamp('registered_at')->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->timestamp('deleted_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
}
