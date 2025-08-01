<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Transfer all organization data to user_details table
        $organizations = DB::table('organizations')->get();
        foreach ($organizations as $org) {
            DB::table('user_details')->insert([
                'org_name' => $org->name,
                'org_size' => $org->size,
                'address' => $org->address1,
                'city' => $org->city,
                'state' => $org->state,
                'zip' => $org->zip,
                'country' => $org->country,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        Schema::dropIfExists('organizations');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse migration for data loss
        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('size')->nullable();
            $table->string('source')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip')->nullable();
            $table->string('country')->nullable();
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
            $table->string('main_contact')->nullable();
            $table->string('admin_email')->nullable();
            $table->string('admin_phone')->nullable();
            $table->string('sales_person')->nullable();
            $table->date('last_contacted')->nullable();
            $table->integer('certified_staff')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }
};
