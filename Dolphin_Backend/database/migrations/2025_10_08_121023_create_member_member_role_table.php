<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMemberMemberRoleTable extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('member_member_role')) {
            Schema::create('member_member_role', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->unsignedBigInteger('member_id');
                $table->unsignedBigInteger('member_role_id');
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('member_member_role');
    }
}
