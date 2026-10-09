<?php

namespace Tests\Feature;

use App\Models\BibleMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class BibleMessageTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'is_active' => true,
        ]);
    }

    protected function makeMessage(string $status, string $title, ?string $date = null): BibleMessage
    {
        return BibleMessage::create([
            'title' => $title,
            'verse' => 'John 3:16',
            'message' => 'For God so loved the world.',
            'publish_date' => $date,
            'published_at' => $status === 'published' ? now() : null,
            'status' => $status,
        ]);
    }

    protected function validData(array $overrides = []): array
    {
        return array_merge([
            'title' => 'Walking in Faith',
            'verse' => 'Hebrews 11:1',
            'message' => 'Faith is confidence in what we hope for.',
            'author' => 'Fellowship Team',
            'status' => 'draft',
        ], $overrides);
    }

    // ---- Public side ----

    public function test_guest_can_view_the_public_list(): void
    {
        $this->get(route('bible-messages.index'))
            ->assertStatus(200);
    }

    public function test_public_list_shows_only_published_messages(): void
    {
        $this->makeMessage('published', 'PublishedOne', today()->toDateString());
        $this->makeMessage('draft', 'DraftOne');
        $this->makeMessage('scheduled', 'ScheduledOne', Carbon::tomorrow()->toDateString());

        $this->get(route('bible-messages.index'))
            ->assertStatus(200)
            ->assertSee('PublishedOne')
            ->assertDontSee('DraftOne')
            ->assertDontSee('ScheduledOne');
    }

    public function test_guest_can_read_a_published_message(): void
    {
        $message = $this->makeMessage('published', 'PublishedOne', today()->toDateString());

        $this->get(route('bible-messages.show', $message))
            ->assertStatus(200)
            ->assertSee('PublishedOne');
    }

    public function test_guest_cannot_read_a_draft_by_guessing_its_id(): void
    {
        $message = $this->makeMessage('draft', 'DraftOne');

        $this->get(route('bible-messages.show', $message))
            ->assertStatus(404);
    }

    public function test_guest_cannot_read_a_scheduled_message_by_guessing_its_id(): void
    {
        $message = $this->makeMessage('scheduled', 'ScheduledOne', Carbon::tomorrow()->toDateString());

        $this->get(route('bible-messages.show', $message))
            ->assertStatus(404);
    }

    // ---- Admin side: access ----

    public function test_main_admin_can_view_the_admin_list(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->get(route('admin.bible-messages.index'))
            ->assertStatus(200);
    }

    public function test_main_admin_can_open_the_create_form(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->get(route('admin.bible-messages.create'))
            ->assertStatus(200);
    }

    public function test_member_cannot_view_the_admin_list(): void
    {
        $member = $this->makeUser('member');

        $this->actingAs($member)
            ->get(route('admin.bible-messages.index'))
            ->assertStatus(403);
    }

    public function test_member_cannot_create_a_message(): void
    {
        $member = $this->makeUser('member');

        $this->actingAs($member)
            ->post(route('admin.bible-messages.store'), $this->validData())
            ->assertStatus(403);

        $this->assertDatabaseCount('bible_messages', 0);
    }

    public function test_guest_cannot_reach_the_admin_list(): void
    {
        $this->get(route('admin.bible-messages.index'))
            ->assertRedirect(route('login'));
    }

    // ---- Admin side: creating, validating, editing ----

    public function test_main_admin_can_save_a_draft(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->post(route('admin.bible-messages.store'), $this->validData())
            ->assertRedirect(route('admin.bible-messages.index'));

        $this->assertDatabaseHas('bible_messages', [
            'title' => 'Walking in Faith',
            'status' => 'draft',
            'created_by' => $admin->id,
        ]);
    }

    public function test_main_admin_can_schedule_a_message_for_a_future_date(): void
    {
        $admin = $this->makeUser('main_admin');
        $date = Carbon::tomorrow()->toDateString();

        $this->actingAs($admin)
            ->post(route('admin.bible-messages.store'), $this->validData([
                'status' => 'scheduled',
                'publish_date' => $date,
            ]))
            ->assertRedirect(route('admin.bible-messages.index'));

        $this->assertDatabaseHas('bible_messages', [
            'title' => 'Walking in Faith',
            'status' => 'scheduled',
        ]);
    }

    public function test_scheduling_without_a_date_is_rejected(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->post(route('admin.bible-messages.store'), $this->validData(['status' => 'scheduled']))
            ->assertSessionHasErrors('publish_date');

        $this->assertDatabaseCount('bible_messages', 0);
    }

    public function test_scheduling_for_a_past_date_is_rejected(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->post(route('admin.bible-messages.store'), $this->validData([
                'status' => 'scheduled',
                'publish_date' => Carbon::yesterday()->toDateString(),
            ]))
            ->assertSessionHasErrors('publish_date');

        $this->assertDatabaseCount('bible_messages', 0);
    }

    public function test_publishing_immediately_stamps_the_published_time(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->post(route('admin.bible-messages.store'), $this->validData(['status' => 'published']));

        $message = BibleMessage::first();

        $this->assertSame('published', $message->status);
        $this->assertNotNull($message->published_at);
    }

    public function test_required_fields_are_enforced(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->post(route('admin.bible-messages.store'), [])
            ->assertSessionHasErrors(['title', 'verse', 'message', 'status']);
    }

    public function test_main_admin_can_edit_a_message(): void
    {
        $admin = $this->makeUser('main_admin');
        $message = $this->makeMessage('draft', 'Original');

        $this->actingAs($admin)
            ->put(route('admin.bible-messages.update', $message), $this->validData(['title' => 'Edited']))
            ->assertRedirect(route('admin.bible-messages.index'));

        $this->assertSame('Edited', $message->fresh()->title);
    }

    public function test_moving_a_published_message_back_to_draft_takes_it_off_the_public_page(): void
    {
        $admin = $this->makeUser('main_admin');
        $message = $this->makeMessage('published', 'PublishedOne', today()->toDateString());

        $this->actingAs($admin)
            ->put(route('admin.bible-messages.update', $message), $this->validData([
                'title' => 'PublishedOne',
                'status' => 'draft',
            ]));

        $this->assertSame('draft', $message->fresh()->status);
        $this->assertNull($message->fresh()->published_at);

        $this->get(route('bible-messages.show', $message))->assertStatus(404);
    }

    public function test_main_admin_can_delete_a_message(): void
    {
        $admin = $this->makeUser('main_admin');
        $message = $this->makeMessage('draft', 'ToDelete');

        $this->actingAs($admin)
            ->delete(route('admin.bible-messages.destroy', $message))
            ->assertRedirect(route('admin.bible-messages.index'));

        $this->assertDatabaseMissing('bible_messages', ['id' => $message->id]);
    }
        public function test_main_admin_can_open_the_edit_form(): void
    {
        $admin = $this->makeUser('main_admin');
        $message = $this->makeMessage('draft', 'Original');

        $this->actingAs($admin)
            ->get(route('admin.bible-messages.edit', $message))
            ->assertStatus(200)
            ->assertSee('Original');
    }
}
