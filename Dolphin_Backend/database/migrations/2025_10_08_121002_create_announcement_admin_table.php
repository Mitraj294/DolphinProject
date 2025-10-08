<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnouncementAdminTable extends Migration
{
    public function up(): void
    {
        Schema::create('announcement_admin', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('announcement_id');
            $table->unsignedBigInteger('admin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_admin');
    }
}
