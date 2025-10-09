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
        if (! Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('stripe_subscription_id')->nullable()->index();
                $table->string('stripe_customer_id')->nullable();
                $table->string('plan')->nullable();
                $table->string('status')->nullable();
                $table->string('payment_method')->nullable();
                $table->dateTime('payment_date')->nullable();
                $table->dateTime('subscription_start')->nullable();
                $table->dateTime('subscription_end')->nullable();
                $table->decimal('amount', 10, 2)->nullable();
                $table->string('receipt_url')->nullable();
                $table->string('invoice_number')->nullable();
                $table->text('description')->nullable();
                $table->string('customer_name')->nullable();
                $table->string('customer_email')->nullable();
                $table->string('customer_country')->nullable();
                $table->timestamps();
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
