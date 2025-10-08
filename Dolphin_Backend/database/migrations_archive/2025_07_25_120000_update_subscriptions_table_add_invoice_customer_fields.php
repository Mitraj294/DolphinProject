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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->string('invoice_number')->nullable()->after('receipt_url');
            $table->text('description')->nullable()->after('invoice_number');
            $table->string('customer_name')->nullable()->after('description');
            $table->string('customer_email')->nullable()->after('customer_name');
            $table->string('customer_country')->nullable()->after('customer_email');
            $table->dateTime('payment_date')->nullable()->change();
            $table->dateTime('subscription_start')->nullable()->change();
            $table->dateTime('subscription_end')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['invoice_number', 'description', 'customer_name', 'customer_email', 'customer_country']);
            $table->date('payment_date')->nullable()->change();
            $table->date('subscription_start')->nullable()->change();
            $table->date('subscription_end')->nullable()->change();
        });
    }
};
