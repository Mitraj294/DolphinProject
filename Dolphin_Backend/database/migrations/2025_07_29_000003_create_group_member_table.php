<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (! Schema::hasTable('group_member')) {
            Schema::create('group_member', function (Blueprint $table) {
                $table->id();
                $table->foreignId('group_id')->constrained('groups')->onDelete('cascade');
                $table->foreignId('member_id')->constrained('members')->onDelete('cascade');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('group_member');
    }
};
