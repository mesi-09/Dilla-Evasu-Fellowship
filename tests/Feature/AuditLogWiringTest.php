<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\BibleMessage;
use App\Models\CoworkerApplication;
use App\Models\ProhibitedWord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogWiringTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    protected function word(): ProhibitedWord
    {
        return ProhibitedWord::create(['word' => 'badword', 'category' => 'profanity', 'is_active' => true]);
    }

    protected function bibleMessage(): BibleMessage
    {
        return BibleMessage::create([
            'title' => 'Walking in Faith',
            'verse' => 'Hebrews 11:1',
            'message' => 'Body text.',
            'status' => 'draft',
        ]);
    }

    protected function bibleData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Walking in Faith',
            'verse' => 'Hebrews 11:1',
            'message' => 'Body text.',
            'status' => 'draft',
        ], $overrides);
    }

    protected function application(string $status = 'pending'): CoworkerApplication
    {
        return CoworkerApplication::create([
            'full_name' => 'SECRET-APPLICANT-NAME',
            'phone_number' => '0922334455',
            'email' => 'secret.applicant@example.test',
            'reason_for_joining' => 'SECRET-REASON-TEXT',
            'status' => $status,
        ]);
    }

    // ---- Prohibited words ----

    public function test_adding_a_prohibited_word_is_logged(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->post(route('admin.prohibited-words.store'), ['word' => 'badword', 'category' => 'profanity'])
            ->assertRedirect();

        $log = AuditLog::first();
        $this->assertSame('prohibited_word.added', $log->action);
        $this->assertSame($admin->id, $log->user_id);
    }

    public function test_toggling_a_prohibited_word_is_logged(): void
    {
        $admin = $this->makeUser('main_admin');
        $word = $this->word();

        $this->actingAs($admin)
            ->patch(route('admin.prohibited-words.toggle', $word))
            ->assertRedirect();

        $log = AuditLog::first();
        $this->assertSame('prohibited_word.toggled', $log->action);
        $this->assertStringContainsString('Deactivated', $log->description);
    }

    public function test_removing_a_prohibited_word_is_logged(): void
    {
        $admin = $this->makeUser('main_admin');
        $word = $this->word();

        $this->actingAs($admin)
            ->delete(route('admin.prohibited-words.destroy', $word))
            ->assertRedirect();

        $log = AuditLog::first();
        $this->assertSame('prohibited_word.removed', $log->action);
        $this->assertStringContainsString('badword', $log->description);
    }

    public function test_a_rejected_duplicate_word_is_not_logged(): void
    {
        $admin = $this->makeUser('main_admin');
        $this->word();

        $this->actingAs($admin)
            ->post(route('admin.prohibited-words.store'), ['word' => 'badword', 'category' => 'profanity'])
            ->assertSessionHasErrors('word');

        $this->assertSame(0, AuditLog::count());
    }

    public function test_a_refused_attempt_by_a_member_is_not_logged(): void
    {
        $member = $this->makeUser('member');

        $this->actingAs($member)
            ->post(route('admin.prohibited-words.store'), ['word' => 'badword', 'category' => 'profanity'])
            ->assertStatus(403);

        $this->assertSame(0, AuditLog::count());
    }

    // ---- Bible messages ----

    public function test_creating_a_bible_message_is_logged(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->post(route('admin.bible-messages.store'), $this->bibleData())
            ->assertRedirect();

        $this->assertSame('bible_message.created', AuditLog::first()->action);
    }

    public function test_updating_a_bible_message_is_logged(): void
    {
        $admin = $this->makeUser('main_admin');
        $message = $this->bibleMessage();

        $this->actingAs($admin)
            ->put(route('admin.bible-messages.update', $message), $this->bibleData(['title' => 'Edited Title']))
            ->assertRedirect();

        $log = AuditLog::first();
        $this->assertSame('bible_message.updated', $log->action);
        $this->assertStringContainsString('Edited Title', $log->description);
    }

    public function test_deleting_a_bible_message_is_logged(): void
    {
        $admin = $this->makeUser('main_admin');
        $message = $this->bibleMessage();

        $this->actingAs($admin)
            ->delete(route('admin.bible-messages.destroy', $message))
            ->assertRedirect();

        $this->assertSame('bible_message.deleted', AuditLog::first()->action);
    }

    public function test_an_invalid_bible_message_is_not_logged(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->post(route('admin.bible-messages.store'), [])
            ->assertSessionHasErrors(['title', 'verse', 'message', 'status']);

        $this->assertSame(0, AuditLog::count());
    }

    // ---- Co-worker applications ----

    public function test_reviewing_an_application_is_logged_without_personal_details(): void
    {
        $admin = $this->makeUser('main_admin');
        $application = $this->application();

        $this->actingAs($admin)
            ->put(route('coworker-applications.update', $application), ['status' => 'accepted'])
            ->assertRedirect();

        $log = AuditLog::first();
        $this->assertSame('coworker_application.reviewed', $log->action);
        $this->assertStringContainsString("#{$application->id}", $log->description);
        $this->assertStringContainsString('Accepted', $log->description);
        $this->assertStringNotContainsString('SECRET-APPLICANT-NAME', $log->description);
        $this->assertStringNotContainsString('0922334455', $log->description);
        $this->assertStringNotContainsString('secret.applicant@example.test', $log->description);
    }

    public function test_saving_the_same_application_status_again_is_not_logged(): void
    {
        $admin = $this->makeUser('main_admin');
        $application = $this->application('pending');

        $this->actingAs($admin)
            ->put(route('coworker-applications.update', $application), ['status' => 'pending'])
            ->assertRedirect();

        $this->assertSame(0, AuditLog::count());
    }
}