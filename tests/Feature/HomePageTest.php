<?php

namespace Tests\Feature;

use App\Models\BibleMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class HomePageTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'is_active' => true,
        ]);
    }

    protected function makeMessage(string $status, string $title, string $date): BibleMessage
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

    public function test_guest_can_view_the_homepage(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('Dilla Evasu Fellowship')
            ->assertSee('Love, Faith, Fellowship and Support');
    }

    public function test_guest_sees_the_main_action_buttons(): void
    {
        $this->get('/')
            ->assertSee('Request Love Sharing')
            ->assertSee('Request Counseling')
            ->assertSee('Join the Community')
            ->assertSee('Become a Co-worker');
    }

    public function test_guest_sees_login_and_register_links(): void
    {
        $this->get('/')
            ->assertSee(route('login'), false)
            ->assertSee(route('register'), false);
    }

    public function test_member_sees_the_request_buttons(): void
    {
        $member = $this->makeUser('member');

        $this->actingAs($member)
            ->get('/')
            ->assertSee('Request Love Sharing')
            ->assertSee('Request Counseling');
    }

    public function test_leaders_do_not_see_the_request_buttons(): void
    {
        $leader = $this->makeUser('love_sharing_leader');

        $this->actingAs($leader)
            ->get('/')
            ->assertStatus(200)
            ->assertDontSee('Request Love Sharing')
            ->assertDontSee('Request Counseling');
    }

    public function test_main_admin_dashboard_link_points_to_the_admin_dashboard(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->get('/')
            ->assertSee(route('admin.dashboard'), false);
    }

    public function test_homepage_shows_the_latest_published_bible_message(): void
    {
        $this->makeMessage('published', 'OlderPublished', Carbon::yesterday()->toDateString());
        $this->makeMessage('published', 'LatestPublished', Carbon::today()->toDateString());

        $this->get('/')
            ->assertSee('LatestPublished')
            ->assertDontSee('OlderPublished');
    }

    public function test_homepage_never_shows_drafts_or_future_scheduled_messages(): void
    {
        $this->makeMessage('published', 'LivePublished', Carbon::today()->toDateString());
        // Dated later than the published one, so they would win if the filter were missing.
        $this->makeMessage('draft', 'SecretDraft', Carbon::tomorrow()->toDateString());
        $this->makeMessage('scheduled', 'FutureScheduled', Carbon::tomorrow()->toDateString());

        $this->get('/')
            ->assertSee('LivePublished')
            ->assertDontSee('SecretDraft')
            ->assertDontSee('FutureScheduled');
    }

    public function test_homepage_still_loads_when_no_message_is_published(): void
    {
        $this->get('/')
            ->assertStatus(200)
            ->assertDontSee('Latest Bible Message');
    }
}