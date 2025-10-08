<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuestLinksTable extends Migration
{
    public function up(): void
    {
        Schema::create('guest_links', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('code', 255)->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->json('meta')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_links');
    }
}
