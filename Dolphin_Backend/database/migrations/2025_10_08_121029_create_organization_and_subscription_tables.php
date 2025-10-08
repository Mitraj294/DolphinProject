<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationAndSubscriptionTables extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('organization_assessment_questions')) {
            Schema::create('organization_assessment_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('text', 255);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            });
        }

        if (! Schema::hasTable('organizations')) {
            Schema::create('organizations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('organization_name', 255)->nullable();
            $table->string('organization_size', 255)->nullable();
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
            $table->unsignedBigInteger('sales_person_id')->nullable()->index();
            $table->dateTime('last_contacted')->nullable();
            $table->integer('certified_staff')->nullable();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            });
        }

        if (! Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('stripe_subscription_id')->nullable()->index();
            $table->string('stripe_customer_id')->nullable();
            $table->string('plan')->nullable();
            $table->string('status')->nullable();
            $table->string('payment_method')->nullable();
            $table->string('default_payment_method_id')->nullable();
            $table->string('payment_method_type')->nullable();
            $table->string('payment_method_brand')->nullable();
            $table->string('payment_method_last4')->nullable();
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
            $table->json('meta')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('organizations');
        Schema::dropIfExists('organization_assessment_questions');
    }
}
