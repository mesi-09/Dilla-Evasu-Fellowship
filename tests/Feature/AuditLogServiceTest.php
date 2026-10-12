<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\BibleMessage;
use App\Models\CoworkerApplication;
use App\Models\ProhibitedWord;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogServiceTest extends TestCase
{
    use RefreshDatabase;

    protected AuditLogService $audit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->audit = new AuditLogService();
    }

    protected function makeAdmin(): User
    {
        return User::factory()->create(['role' => 'main_admin', 'is_active' => true, 'name' => 'Admin Alem']);
    }

    public function test_an_entry_records_who_did_what(): void
    {
        $admin = $this->makeAdmin();
        $word = ProhibitedWord::create(['word' => 'badword', 'category' => 'profanity', 'is_active' => true]);

        $this->audit->prohibitedWordAdded($admin, $word);

        $log = AuditLog::first();
        $this->assertSame($admin->id, $log->user_id);
        $this->assertSame('Admin Alem', $log->actor_name);
        $this->assertSame('prohibited_word.added', $log->action);
        $this->assertStringContainsString('badword', $log->description);
        $this->assertStringContainsString('profanity', $log->description);
        $this->assertNotNull($log->created_at);
    }

    public function test_the_entry_survives_and_keeps_the_name_when_the_admin_is_deleted(): void
    {
        $admin = $this->makeAdmin();
        $word = ProhibitedWord::create(['word' => 'badword', 'category' => 'profanity', 'is_active' => true]);
        $this->audit->prohibitedWordAdded($admin, $word);

        $admin->delete();

        $log = AuditLog::first();
        $this->assertNotNull($log);
        $this->assertNull($log->user_id);
        $this->assertSame('Admin Alem', $log->actor_name);
    }

    public function test_toggling_says_whether_the_word_was_activated_or_deactivated(): void
    {
        $admin = $this->makeAdmin();
        $word = ProhibitedWord::create(['word' => 'badword', 'category' => 'profanity', 'is_active' => false]);

        $this->audit->prohibitedWordToggled($admin, $word);
        $word->update(['is_active' => true]);
        $this->audit->prohibitedWordToggled($admin, $word->fresh());

        $descriptions = AuditLog::orderBy('id')->pluck('description');
        $this->assertStringContainsString('Deactivated', $descriptions[0]);
        $this->assertStringContainsString('Activated', $descriptions[1]);
    }

    public function test_a_scheduled_bible_message_logs_its_date(): void
    {
        $admin = $this->makeAdmin();
        $message = BibleMessage::create([
            'title' => 'Walking in Faith',
            'verse' => 'Hebrews 11:1',
            'message' => 'Body text.',
            'publish_date' => '2026-12-25',
            'status' => 'scheduled',
        ]);

        $this->audit->bibleMessageCreated($admin, $message);

        $description = AuditLog::first()->description;
        $this->assertStringContainsString('Walking in Faith', $description);
        $this->assertStringContainsString('Scheduled for Dec 25, 2026', $description);
    }

    public function test_bible_message_update_and_delete_are_logged(): void
    {
        $admin = $this->makeAdmin();
        $message = BibleMessage::create([
            'title' => 'Walking in Faith',
            'verse' => 'Hebrews 11:1',
            'message' => 'Body text.',
            'status' => 'draft',
        ]);

        $this->audit->bibleMessageUpdated($admin, $message);
        $this->audit->bibleMessageDeleted($admin, $message);

        $this->assertSame(
            ['bible_message.updated', 'bible_message.deleted'],
            AuditLog::orderBy('id')->pluck('action')->all()
        );
    }

    public function test_a_coworker_review_never_logs_the_applicants_personal_details(): void
    {
        $admin = $this->makeAdmin();
        $application = CoworkerApplication::create([
            'full_name' => 'SECRET-APPLICANT-NAME',
            'phone_number' => '0922334455',
            'email' => 'secret.applicant@example.test',
            'reason_for_joining' => 'SECRET-REASON-TEXT',
            'status' => 'accepted',
        ]);

        $this->audit->coworkerApplicationReviewed($admin, $application);

        $description = AuditLog::first()->description;
        $this->assertStringContainsString("#{$application->id}", $description);
        $this->assertStringContainsString('Accepted', $description);
        $this->assertStringNotContainsString('SECRET-APPLICANT-NAME', $description);
        $this->assertStringNotContainsString('0922334455', $description);
        $this->assertStringNotContainsString('secret.applicant@example.test', $description);
        $this->assertStringNotContainsString('SECRET-REASON-TEXT', $description);
    }

    public function test_a_very_long_description_is_cut_to_fit(): void
    {
        $admin = $this->makeAdmin();
        $message = BibleMessage::create([
            'title' => str_repeat('A', 255),
            'verse' => 'Hebrews 11:1',
            'message' => 'Body text.',
            'status' => 'draft',
        ]);

        $this->audit->bibleMessageCreated($admin, $message);

        $this->assertLessThanOrEqual(500, strlen(AuditLog::first()->description));
    }
}