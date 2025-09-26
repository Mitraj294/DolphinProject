<?php

namespace Tests\Feature;

use App\Jobs\SendScheduledAssessmentEmail;
use App\Models\Assessment;
use App\Models\AssessmentSchedule;
use App\Models\AssessmentAnswerToken;
use App\Models\Group;
use App\Models\Member;
use App\Models\Organization;
use App\Models\Role;
use App\Models\ScheduledEmail;
use App\Models\User;
use App\Notifications\AssessmentInvitation;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AssessmentSchedulingFlowTest extends TestCase
{
    use DatabaseTransactions;

    public function test_full_assessment_scheduling_flow_creates_records_and_dispatches_job(): void
    {
        Notification::fake();
        Bus::fake();

        putenv('FRONTEND_URL=http://frontend.test');
        $_ENV['FRONTEND_URL'] = 'http://frontend.test';
        $_SERVER['FRONTEND_URL'] = 'http://frontend.test';

        $user = User::create([
            'first_name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin+flow@example.com',
            'password' => bcrypt('secret'),
        ]);

        $role = Role::firstOrCreate(['name' => 'superadmin']);
        $user->roles()->syncWithoutDetaching([$role->id]);

        $organization = Organization::create([
            'organization_name' => 'Acme Inc',
            'user_id' => $user->id,
        ]);

        $assessment = Assessment::create([
            'name' => 'Quarterly Health Check',
            'user_id' => $user->id,
            'organization_id' => $organization->id,
        ]);

        $member = Member::create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john+flow@example.com',
            'phone' => '1234567890',
            'user_id' => $user->id,
        ]);

        $group = Group::create([
            'name' => 'Pilot Group',
            'user_id' => $user->id,
        ]);
        $group->members()->attach($member->id);

        $localTimezone = 'Asia/Kolkata';
        $localDateTime = Carbon::now($localTimezone)->addHour();
        $sendAtUtc = $localDateTime->copy()->setTimezone('UTC');

        $schedulePayload = [
            'assessment_id' => $assessment->id,
            'date' => $localDateTime->toDateString(),
            'time' => $localDateTime->format('H:i:s'),
            'send_at' => $sendAtUtc->toIso8601String(),
            'timezone' => $localTimezone,
            'group_ids' => [$group->id],
            'member_ids' => [$member->id],
        ];

        $this->withoutMiddleware();

        $scheduleResponse = $this->postJson('/api/assessment-schedules', $schedulePayload);
        $scheduleResponse->assertStatus(200)->assertJson(['scheduled' => true]);

        $schedule = AssessmentSchedule::where('assessment_id', $assessment->id)
            ->latest('id')
            ->first();
        $this->assertNotNull($schedule);
        $this->assertEquals($assessment->id, $schedule->assessment_id);
        $this->assertEquals([$group->id], $schedule->group_ids);
        $this->assertEquals([$member->id], $schedule->member_ids);

        $this->assertTrue(
            AssessmentAnswerToken::where('assessment_id', $assessment->id)
                ->where('member_id', $member->id)
                ->exists()
        );

        Notification::assertSentTo(
            $member,
            AssessmentInvitation::class,
            function ($notification, $channels) {
                return $notification instanceof AssessmentInvitation
                    && in_array('mail', $channels, true);
            }
        );

        $emailPayload = [
            'recipient_email' => $member->email,
            'subject' => 'Assessment Reminder',
            'body' => 'Please complete your assessment.',
            'send_at' => $sendAtUtc->toIso8601String(),
            'assessment_id' => $assessment->id,
            'group_id' => $group->id,
            'member_id' => $member->id,
        ];

        $emailResponse = $this->postJson('/api/schedule-email', $emailPayload);
        $emailResponse->assertStatus(201)->assertJsonFragment([
            'recipient_email' => $member->email,
            'assessment_id' => $assessment->id,
        ]);

        $scheduledEmail = ScheduledEmail::first();
        $this->assertNotNull($scheduledEmail);
    $this->assertEquals($member->email, $scheduledEmail->recipient_email);
        $this->assertEquals($assessment->id, $scheduledEmail->assessment_id);
        $this->assertEquals($group->id, $scheduledEmail->group_id);
        $this->assertEquals($member->id, $scheduledEmail->member_id);
        $this->assertEquals(
            $sendAtUtc->copy()->setTimezone('UTC')->toDateTimeString(),
            Carbon::parse($scheduledEmail->send_at)->setTimezone('UTC')->toDateTimeString()
        );

        Bus::assertDispatched(SendScheduledAssessmentEmail::class, function ($job) use ($scheduledEmail) {
            return $job->scheduledEmailId === $scheduledEmail->id;
        });

        $showResponse = $this->getJson('/api/scheduled-email/show?assessment_id=' . $assessment->id);
        $showResponse->assertStatus(200)
            ->assertJson(['scheduled' => true])
            ->assertJsonPath('emails.0.recipient_email', $member->email)
            ->assertJsonPath('members_with_details.0.email', $member->email);
    }
}
