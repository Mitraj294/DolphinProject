<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Baseline Schema Migration
 * 
 * This migration creates the complete database schema for DolphinProject.
 * It replaces all previous incremental migrations with a single comprehensive migration.
 * 
 * Created: 2025-01-01
 * Squashes: 74 previous migrations from 2025-07-17 through 2025-10-08
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ============================================
        // Laravel Framework Tables
        // ============================================
        
        // Cache tables
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->integer('expiration');
        });

        // Job queue tables
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedTinyInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // Session table
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        // Password reset tokens
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Laravel notifications (standard table for queued notifications)
        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // ============================================
        // Laravel Passport OAuth Tables
        // ============================================

        Schema::create('oauth_auth_codes', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('client_id');
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_access_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('client_id');
            $table->string('name')->nullable();
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->timestamps();
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_refresh_tokens', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->string('access_token_id', 100)->index();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('name');
            $table->string('secret', 100)->nullable();
            $table->string('provider')->nullable();
            $table->text('redirect');
            $table->boolean('personal_access_client');
            $table->boolean('password_client');
            $table->boolean('revoked');
            $table->timestamps();
        });

        Schema::create('oauth_device_codes', function (Blueprint $table) {
            $table->string('id', 100)->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->unsignedBigInteger('client_id')->index();
            $table->string('user_code')->unique();
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->dateTime('user_approved_at')->nullable();
            $table->dateTime('last_polled_at')->nullable();
            $table->dateTime('expires_at')->nullable();
        });

        // ============================================
        // Location Reference Tables
        // ============================================

        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 2)->nullable();
        });

        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->string('name');
            $table->string('code', 10)->nullable();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
        });

        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->string('name');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('set null');
        });

        // ============================================
        // User Management Tables
        // ============================================

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('email');
            $table->string('password');
            $table->rememberToken();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Email should be unique excluding soft-deleted records
            // Note: unique constraint will be added after index creation
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamps();
            $table->softDeletes();
            
            $table->primary(['user_id', 'role_id']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });

        Schema::create('user_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('phone')->nullable();
            $table->string('find_us')->nullable();
            $table->string('address')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('zip')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
        });

        // ============================================
        // Organization Management Tables
        // ============================================

        Schema::create('organizations', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name');
            $table->string('organization_size')->nullable();
            $table->string('source')->nullable();
            $table->string('address1')->nullable();
            $table->string('address2')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('zip')->nullable();
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();
            $table->string('main_contact')->nullable();
            $table->string('admin_email')->nullable();
            $table->string('admin_phone')->nullable();
            $table->date('last_contacted')->nullable();
            $table->integer('certified_staff')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sales_person_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('sales_person_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
        });

        // Add organization_id foreign key to users after organizations table exists
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');
        });

        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
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
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // ============================================
        // Lead Management Tables
        // ============================================

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('find_us')->nullable();
            $table->string('organization_name')->nullable();
            $table->string('organization_size')->nullable();
            $table->string('address')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('zip')->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('Lead Stage');
            $table->timestamp('assessment_sent_at')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('sales_person_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('sales_person_id')->references('id')->on('users')->onDelete('set null');
        });

        // ============================================
        // Member and Group Management Tables
        // ============================================

        Schema::create('groups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('member_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('group_member', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('member_id');
            $table->timestamps();
            
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
        });

        Schema::create('member_member_role', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('member_role_id');
            $table->timestamps();
            
            $table->unique(['member_id', 'member_role_id']);
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('member_role_id')->references('id')->on('member_roles')->onDelete('cascade');
        });

        // ============================================
        // Assessment System Tables
        // ============================================

        Schema::create('organization_assessment_questions', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->timestamps();
        });

        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('set null');
        });

        Schema::create('assessment_question', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedBigInteger('question_id');
            $table->timestamps();
            
            $table->unique(['assessment_id', 'question_id']);
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
            $table->foreign('question_id')->references('id')->on('organization_assessment_questions')->onDelete('cascade');
        });

        Schema::create('assessment_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->json('group_ids')->nullable();
            $table->json('member_ids')->nullable();
            $table->timestamps();
            
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
        });

        Schema::create('scheduled_emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id')->nullable();
            $table->unsignedBigInteger('group_id')->nullable();
            $table->unsignedBigInteger('member_id')->nullable();
            $table->string('recipient_email');
            $table->string('subject');
            $table->text('body');
            $table->dateTime('send_at')->nullable();
            $table->boolean('sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->index('assessment_id');
            $table->index('group_id');
            $table->index('member_id');
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->nullOnDelete();
            $table->foreign('member_id')->references('id')->on('members')->nullOnDelete();
        });

        Schema::create('assessment_answer_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->string('token')->unique();
            $table->dateTime('expires_at')->nullable();
            $table->boolean('used')->default(false);
            $table->timestamps();
            
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
        });

        Schema::create('assessment_answer_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedBigInteger('member_id');
            $table->string('token')->unique();
            $table->boolean('completed')->default(false);
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();
            
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
        });

        Schema::create('assessment_question_answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedBigInteger('organization_assessment_question_id');
            $table->unsignedBigInteger('assessment_question_id');
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->text('answer');
            $table->timestamps();
            
            $table->unique(['assessment_id', 'organization_assessment_question_id', 'member_id', 'group_id'], 'aqa_unique');
            $table->foreign('assessment_id')->references('id')->on('assessments')->onDelete('cascade');
            $table->foreign('organization_assessment_question_id')->references('id')->on('organization_assessment_questions')->onDelete('cascade');
            $table->foreign('assessment_question_id')->references('id')->on('assessment_question')->onDelete('cascade');
            $table->foreign('member_id')->references('id')->on('members')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
        });

        // Legacy tables that may still be referenced
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->text('text');
            $table->json('options')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('answers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('assessment_question_id')->nullable();
            $table->string('question');
            $table->text('answer');
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assessment_question_id')->references('id')->on('assessment_question')->onDelete('set null');
        });

        // ============================================
        // Announcement System Tables
        // ============================================

        Schema::create('announcements', function (Blueprint $table) {
            $table->id();
            $table->text('body');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
            
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('announcement_organization', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('announcement_id');
            $table->unsignedBigInteger('organization_id');
            
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->foreign('organization_id')->references('id')->on('organizations')->onDelete('cascade');
        });

        Schema::create('announcement_admin', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('announcement_id');
            $table->unsignedBigInteger('admin_id');
            
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('announcement_group', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('announcement_id');
            $table->unsignedBigInteger('group_id');
            
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('cascade');
        });

        // ============================================
        // Guest Access Tables
        // ============================================

        Schema::create('guest_links', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('lead_id')->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // ============================================
        // Add unique indexes that need special handling
        // ============================================
        
        // For SQLite and MySQL, create unique indexes that respect soft deletes
        // This allows the same email to be reused after soft deletion
        Schema::table('users', function (Blueprint $table) {
            $table->unique('email');
        });

        Schema::table('members', function (Blueprint $table) {
            $table->unique('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order to respect foreign key constraints
        Schema::dropIfExists('guest_links');
        Schema::dropIfExists('announcement_group');
        Schema::dropIfExists('announcement_admin');
        Schema::dropIfExists('announcement_organization');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('answers');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('assessment_question_answers');
        Schema::dropIfExists('assessment_answer_links');
        Schema::dropIfExists('assessment_answer_tokens');
        Schema::dropIfExists('scheduled_emails');
        Schema::dropIfExists('assessment_schedules');
        Schema::dropIfExists('assessment_question');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('organization_assessment_questions');
        Schema::dropIfExists('member_member_role');
        Schema::dropIfExists('group_member');
        Schema::dropIfExists('members');
        Schema::dropIfExists('member_roles');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('organizations');
        Schema::dropIfExists('user_details');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('users');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('states');
        Schema::dropIfExists('countries');
        Schema::dropIfExists('oauth_device_codes');
        Schema::dropIfExists('oauth_clients');
        Schema::dropIfExists('oauth_refresh_tokens');
        Schema::dropIfExists('oauth_access_tokens');
        Schema::dropIfExists('oauth_auth_codes');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
    }
};
