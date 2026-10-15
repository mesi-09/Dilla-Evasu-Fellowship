<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogPageTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    protected function entry(User $actor, string $action, string $description): AuditLog
    {
        return AuditLog::create([
            'user_id' => $actor->id,
            'actor_name' => $actor->name,
            'action' => $action,
            'description' => $description,
        ]);
    }

    public function test_main_admin_can_read_the_log(): void
    {
        $admin = $this->makeUser('main_admin');
        $this->entry($admin, 'prohibited_word.added', 'Added prohibited word "alpha" (profanity).');

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index'))
            ->assertStatus(200)
            ->assertSee('Added prohibited word')
            ->assertSee($admin->name);
    }

    public function test_the_page_loads_when_nothing_has_been_logged(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index'))
            ->assertStatus(200)
            ->assertSee('Nothing has been logged yet.');
    }

    public function test_newest_entries_come_first(): void
    {
        $admin = $this->makeUser('main_admin');
        $this->entry($admin, 'prohibited_word.added', 'FirstEntry');
        $this->entry($admin, 'prohibited_word.added', 'SecondEntry');

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index'))
            ->assertSeeInOrder(['SecondEntry', 'FirstEntry']);
    }

    public function test_the_log_can_be_filtered_by_action(): void
    {
        $admin = $this->makeUser('main_admin');
        $this->entry($admin, 'prohibited_word.added', 'WordEntryText');
        $this->entry($admin, 'bible_message.created', 'BibleEntryText');

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index', ['action' => 'prohibited_word.added']))
            ->assertStatus(200)
            ->assertSee('WordEntryText')
            ->assertDontSee('BibleEntryText');
    }

    public function test_html_in_an_entry_is_escaped(): void
    {
        $admin = $this->makeUser('main_admin');
        $this->entry($admin, 'bible_message.created', '<script>alert(1)</script>');

        $this->actingAs($admin)
            ->get(route('admin.audit-logs.index'))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;', false);
    }

    public function test_members_and_leaders_cannot_read_the_log(): void
    {
        foreach (['member', 'love_sharing_leader', 'counseling_leader'] as $role) {
            $this->actingAs($this->makeUser($role))
                ->get(route('admin.audit-logs.index'))
                ->assertStatus(403);
        }
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get(route('admin.audit-logs.index'))
            ->assertRedirect(route('login'));
    }

    public function test_the_log_cannot_be_changed_through_any_route(): void
    {
        $admin = $this->makeUser('main_admin');
        $entry = $this->entry($admin, 'prohibited_word.added', 'KeepMe');

        $this->actingAs($admin)->delete('/admin/audit-logs')->assertStatus(405);
        $this->actingAs($admin)->put('/admin/audit-logs')->assertStatus(405);
        $this->actingAs($admin)->post('/admin/audit-logs')->assertStatus(405);
        $this->actingAs($admin)->delete('/admin/audit-logs/'.$entry->id)->assertStatus(404);

        $this->assertSame(1, AuditLog::count());
    }

    public function test_the_dashboard_links_to_the_log(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertSee(route('admin.audit-logs.index'), false);
    }
}