<?php

namespace Tests\Feature;

use App\Models\Announcement;
use App\Models\AuditLog;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnnouncementAuditTest extends TestCase
{
    use RefreshDatabase;

    protected function admin(): User
    {
        return User::factory()->create(['role' => 'main_admin', 'is_active' => true, 'name' => 'Admin Alem']);
    }

    protected function announcement(array $overrides = []): Announcement
    {
        return Announcement::create(array_merge([
            'title' => 'Bible study moved',
            'body' => 'BODY-TEXT-THAT-MUST-NOT-BE-LOGGED',
            'status' => 'draft',
        ], $overrides));
    }

    public function test_creating_an_announcement_is_logged_with_its_title_and_state(): void
    {
        $admin = $this->admin();

        (new AuditLogService())->announcementCreated($admin, $this->announcement(['status' => 'published', 'is_important' => true]));

        $log = AuditLog::first();
        $this->assertSame('announcement.created', $log->action);
        $this->assertSame($admin->id, $log->user_id);
        $this->assertStringContainsString('Bible study moved', $log->description);
        $this->assertStringContainsString('Published, important', $log->description);
    }

    public function test_a_draft_is_logged_as_a_draft(): void
    {
        (new AuditLogService())->announcementCreated($this->admin(), $this->announcement());

        $this->assertStringContainsString('(Draft)', AuditLog::first()->description);
    }

    public function test_updating_and_deleting_are_logged(): void
    {
        $admin = $this->admin();
        $announcement = $this->announcement();
        $audit = new AuditLogService();

        $audit->announcementUpdated($admin, $announcement);
        $audit->announcementDeleted($admin, $announcement);

        $this->assertSame(
            ['announcement.updated', 'announcement.deleted'],
            AuditLog::orderBy('id')->pluck('action')->all()
        );
    }

    public function test_the_announcement_text_is_never_logged(): void
    {
        $admin = $this->admin();
        $announcement = $this->announcement();
        $audit = new AuditLogService();

        $audit->announcementCreated($admin, $announcement);
        $audit->announcementUpdated($admin, $announcement);
        $audit->announcementDeleted($admin, $announcement);

        foreach (AuditLog::all() as $log) {
            $this->assertStringNotContainsString('BODY-TEXT-THAT-MUST-NOT-BE-LOGGED', $log->description);
        }
    }
}