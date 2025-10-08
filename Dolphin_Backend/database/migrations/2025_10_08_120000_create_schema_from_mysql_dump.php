<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // small orchestration that delegates to helper methods to keep method size small
        $this->createCacheTables();
        $this->createUserAndRoleTables();
        $this->createAnnouncementTables();
        $this->createAssessmentTables();
        $this->createLocationTables();
        $this->createQueueTables();
        $this->createLeadsAndMembersTables();
        $this->createNotificationsAndOauthTables();
        $this->createOrganizationAndSubscriptionTables();
        $this->createMiscTables();
    }

    private function createCacheTables()
    {
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key', 255)->primary();
            $table->mediumText('value');
            $table->integer('expiration');
        });

        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key', 255)->primary();
            $table->string('owner', 255);
            $table->integer('expiration');
        });
    }


    private function createUserAndRoleTables()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email')->nullable(false)->unique(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->unique();
            $table->timestamps();
        });

        Schema::create('user_roles', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('role_id');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->primary(['user_id', 'role_id']);
        });
    }


    private function createAnnouncementTables()
    {
        Schema::create('announcements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->text('body');
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('dispatched_at')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('announcement_admin', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('announcement_id');
            $table->unsignedBigInteger('admin_id');
        });

        Schema::create('announcement_group', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('announcement_id');
            $table->unsignedBigInteger('group_id');
        });

        Schema::create('announcement_organization', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('announcement_id');
            $table->unsignedBigInteger('organization_id');
        });
    }


    private function createAssessmentTables()
    {
        Schema::create('answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('question', 255);
            $table->text('answer');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('assessment_answer_tokens', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->string('token', 255)->unique();
            $table->timestamp('expires_at')->nullable();
            $table->boolean('used')->default(false);
            $table->timestamps();
        });

        Schema::create('assessment_question', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedBigInteger('question_id');
            $table->timestamps();
        });

        Schema::create('assessment_question_answers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('assessment_id');
            $table->unsignedBigInteger('organization_assessment_question_id');
            $table->unsignedBigInteger('assessment_question_id');
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('group_id')->nullable();
            $table->text('answer');
            $table->timestamps();
        });

        Schema::create('assessment_schedules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('assessment_id');
            $table->date('date')->nullable();
            $table->time('time')->nullable();
            $table->json('group_ids')->nullable();
            $table->json('member_ids')->nullable();
            $table->timestamps();
        });

        Schema::create('assessments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->string('name', 255);
            $table->timestamps();
            $table->softDeletes();
        });
    }


    private function createLocationTables()
    {
        Schema::create('cities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->mediumInteger('state_id')->unsigned();
            $table->mediumInteger('country_id')->unsigned();
            $table->timestamp('created_at')->default('2014-01-01 06:31:01');
            $table->timestamp('updated_at')->useCurrentOnUpdate();
        });

        Schema::create('countries', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrentOnUpdate();
        });

        Schema::create('states', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->mediumInteger('country_id')->unsigned();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->useCurrentOnUpdate();
        });
    }


    private function createQueueTables()
    {
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('uuid');
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->string('name', 255);
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('queue', 255)->index();
            $table->longText('payload');
            $table->tinyInteger('attempts')->unsigned();
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('jobs_backup', function (Blueprint $table) {
            $table->bigInteger('id')->default(0);
            $table->string('queue', 255);
            $table->longText('payload');
            $table->tinyInteger('attempts')->unsigned();
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });
    }


    private function createLeadsAndMembersTables()
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->string('email', 255);
            $table->string('phone', 255)->nullable();
            $table->string('find_us', 255)->nullable();
            $table->string('organization_name', 255)->nullable();
            $table->string('organization_size', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('zip', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('status')->default('Lead Stage');
            $table->timestamp('assessment_sent_at')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('member_member_role', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('member_id');
            $table->unsignedBigInteger('member_role_id');
            $table->timestamps();
        });

        Schema::create('member_roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->unique();
            $table->timestamps();
        });

        Schema::create('members', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('organization_id')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('first_name', 255);
            $table->string('last_name', 255);
            $table->string('email', 255)->index();
            $table->string('phone', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }


    private function createNotificationsAndOauthTables()
    {
        Schema::create('migrations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('migration', 255);
            $table->integer('batch');
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('type', 255);
            $table->string('notifiable_type', 255)->index();
            $table->unsignedBigInteger('notifiable_id');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('oauth_access_tokens', function (Blueprint $table) {
            $table->char('id', 80)->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->char('client_id', 36);
            $table->string('name', 255)->nullable();
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_auth_codes', function (Blueprint $table) {
            $table->char('id', 80)->primary();
            $table->unsignedBigInteger('user_id');
            $table->char('client_id', 36);
            $table->text('scopes')->nullable();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_clients', function (Blueprint $table) {
            $table->char('id', 36)->primary();
            $table->string('owner_type', 255)->nullable()->index();
            $table->unsignedBigInteger('owner_id')->nullable();
            $table->string('name', 255);
            $table->string('secret', 255)->nullable();
            $table->string('provider', 255)->nullable();
            $table->text('redirect_uris');
            $table->text('grant_types');
            $table->boolean('revoked');
            $table->timestamps();
        });

        Schema::create('oauth_device_codes', function (Blueprint $table) {
            $table->char('id', 80)->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->char('client_id', 36)->index();
            $table->char('user_code', 8)->unique();
            $table->text('scopes');
            $table->boolean('revoked');
            $table->dateTime('user_approved_at')->nullable();
            $table->dateTime('last_polled_at')->nullable();
            $table->dateTime('expires_at')->nullable();
        });

        Schema::create('oauth_refresh_tokens', function (Blueprint $table) {
            $table->char('id', 80)->primary();
            $table->char('access_token_id', 80)->index();
            $table->boolean('revoked');
            $table->dateTime('expires_at')->nullable();
        });
    }


    private function createOrganizationAndSubscriptionTables()
    {
        Schema::create('organization_assessment_questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('text', 255);
            $table->timestamps();
        });

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
            $table->timestamps();
            $table->softDeletes();
        });

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
            $table->timestamps();
            $table->softDeletes();
        });
    }


    private function createMiscTables()
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 255)->index();
            $table->string('token', 255);
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('question', 255);
            $table->json('options')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('scheduled_emails', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('assessment_id')->nullable()->index();
            $table->unsignedBigInteger('group_id')->nullable()->index();
            $table->unsignedBigInteger('member_id')->nullable()->index();
            $table->string('recipient_email', 255);
            $table->string('subject', 255);
            $table->text('body');
            $table->dateTime('send_at')->nullable();
            $table->boolean('sent')->default(false);
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id', 255)->primary();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
            $table->timestamp('deleted_at')->nullable();
        });

        Schema::create('user_details', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('phone', 255)->nullable();
            $table->string('find_us', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->unsignedBigInteger('country_id')->nullable()->index();
            $table->unsignedBigInteger('state_id')->nullable()->index();
            $table->unsignedBigInteger('city_id')->nullable()->index();
            $table->string('zip', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $tables = [
            'cache', 'cache_locks', 'users', 'roles', 'user_roles', 'announcements',
            'announcement_admin', 'announcement_group', 'announcement_organization', 'answers',
            'assessment_answer_tokens', 'assessment_question', 'assessment_question_answers',
            'assessment_schedules', 'assessments', 'cities', 'countries', 'failed_jobs',
            'group_member', 'guest_links', 'job_batches', 'jobs', 'jobs_backup', 'leads',
            'member_member_role', 'member_roles', 'members', 'migrations', 'notifications',
            'oauth_access_tokens', 'oauth_auth_codes', 'oauth_clients', 'oauth_device_codes',
            'oauth_refresh_tokens', 'organization_assessment_questions', 'organizations',
            'password_reset_tokens', 'questions', 'scheduled_emails', 'sessions', 'states',
            'subscriptions', 'user_details', 'user_roles'
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }
};
