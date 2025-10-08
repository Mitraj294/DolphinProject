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
        if (!Schema::hasTable('assessment_schedules')) {
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
        } else {
            Schema::table('assessment_schedules', function (Blueprint $table) {
                if (!Schema::hasColumn('assessment_schedules', 'date')) {
                    $table->date('date')->nullable();
                }
                if (!Schema::hasColumn('assessment_schedules', 'time')) {
                    $table->time('time')->nullable();
                }
                if (!Schema::hasColumn('assessment_schedules', 'group_ids')) {
                    $table->json('group_ids')->nullable();
                }
                if (!Schema::hasColumn('assessment_schedules', 'member_ids')) {
                    $table->json('member_ids')->nullable();
                }
            });
        }

        if (!Schema::hasTable('scheduled_emails')) {
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
        } else {
            Schema::table('scheduled_emails', function (Blueprint $table) {
                if (!Schema::hasColumn('scheduled_emails', 'assessment_id')) {
                    $table->unsignedBigInteger('assessment_id')->nullable()->after('id');
                    $table->index('assessment_id', 'scheduled_emails_assessment_id_index');
                }
                if (!Schema::hasColumn('scheduled_emails', 'group_id')) {
                    $table->unsignedBigInteger('group_id')->nullable()->after('assessment_id');
                    $table->index('group_id', 'scheduled_emails_group_id_index');
                }
                if (!Schema::hasColumn('scheduled_emails', 'member_id')) {
                    $table->unsignedBigInteger('member_id')->nullable()->after('group_id');
                    $table->index('member_id', 'scheduled_emails_member_id_index');
                }
                if (!Schema::hasColumn('scheduled_emails', 'recipient_email')) {
                    $table->string('recipient_email')->after('member_id');
                }
                if (!Schema::hasColumn('scheduled_emails', 'subject')) {
                    $table->string('subject')->after('recipient_email');
                }
                if (!Schema::hasColumn('scheduled_emails', 'body')) {
                    $table->text('body')->after('subject');
                }
                if (!Schema::hasColumn('scheduled_emails', 'send_at')) {
                    $table->dateTime('send_at')->nullable()->after('body');
                }
                if (!Schema::hasColumn('scheduled_emails', 'sent')) {
                    $table->boolean('sent')->default(false)->after('send_at');
                }
                if (!Schema::hasColumn('scheduled_emails', 'sent_at')) {
                    $table->timestamp('sent_at')->nullable()->after('sent');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('scheduled_emails')) {
            Schema::drop('scheduled_emails');
        }

        if (Schema::hasTable('assessment_schedules')) {
            Schema::drop('assessment_schedules');
        }
    }
};
