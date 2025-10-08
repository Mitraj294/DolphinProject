<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnnouncementOrganizationTable extends Migration
{
    public function up(): void
    {
        Schema::create('announcement_organization', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('announcement_id');
            $table->unsignedBigInteger('organization_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('announcement_organization');
    }
}
