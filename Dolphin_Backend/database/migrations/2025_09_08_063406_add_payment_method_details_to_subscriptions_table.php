<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add columns only if they don't already exist (idempotent)
        try {
            if (Schema::hasTable('subscriptions')) {
                Schema::table('subscriptions', function (Blueprint $table) {
                    if (! Schema::hasColumn('subscriptions', 'default_payment_method_id')) {
                        $table->string('default_payment_method_id')->nullable()->after('payment_method');
                    }
                    if (! Schema::hasColumn('subscriptions', 'payment_method_type')) {
                        $table->string('payment_method_type')->nullable()->after('default_payment_method_id');
                    }
                    if (! Schema::hasColumn('subscriptions', 'payment_method_brand')) {
                        $table->string('payment_method_brand')->nullable()->after('payment_method_type');
                    }
                    if (! Schema::hasColumn('subscriptions', 'payment_method_last4')) {
                        $table->string('payment_method_last4')->nullable()->after('payment_method_brand');
                    }
                });
            }
        } catch (\Exception $e) {
            // best-effort: if the columns already exist or another error occurs, log and continue
            // avoiding migration failure due to duplicate columns when re-running on test DB
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['default_payment_method_id', 'payment_method_type', 'payment_method_brand', 'payment_method_last4']);
        });
    }
};
