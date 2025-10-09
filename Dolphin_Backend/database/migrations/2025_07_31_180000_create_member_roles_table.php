<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (! Schema::hasTable('member_roles')) {
            Schema::create('member_roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('member_member_role')) {
            Schema::create('member_member_role', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('member_id');
                $table->unsignedBigInteger('member_role_id');
                $table->timestamps();
                $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
                $table->foreign('member_role_id')->references('id')->on('member_roles')->onDelete('cascade');
                $table->unique(['member_id', 'member_role_id']);
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('member_member_role');
        Schema::dropIfExists('member_roles');
    }
};
