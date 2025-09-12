<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->unsignedBigInteger('sales_person_id')->nullable()->after('contract_end');
            $table->index('sales_person_id');
            $table->foreign('sales_person_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (Schema::hasColumn('organizations', 'sales_person_id')) {
                $table->dropForeign(['sales_person_id']);
                $table->dropIndex(['sales_person_id']);
                $table->dropColumn('sales_person_id');
            }
        });
    }
};
