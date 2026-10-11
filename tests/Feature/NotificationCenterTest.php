<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationCenterTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role = 'member'): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    public function test_user_sees_their_own_notifications(): void
    {
        $user = $this->makeUser();
        $user->notify(new AppNotification('Your request was submitted.', '/love-sharing/1'));

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertStatus(200)
            ->assertSee('Your request was submitted.');
    }

    public function test_user_does_not_see_someone_elses_notifications(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser();
        $other->notify(new AppNotification('OTHER-PERSONS-NOTE', '/love-sharing/1'));

        $this->actingAs($user)
            ->get(route('notifications.index'))
            ->assertStatus(200)
            ->assertDontSee('OTHER-PERSONS-NOTE');
    }

    public function test_clicking_a_notification_marks_it_read_and_goes_to_its_page(): void
    {
        $user = $this->makeUser();
        $user->notify(new AppNotification('Hi', '/love-sharing/1'));
        $notification = $user->notifications()->first();

        $this->actingAs($user)
            ->post(route('notifications.read', $notification->id))
            ->assertRedirect('/love-sharing/1');

        $this->assertNotNull($notification->fresh()->read_at);
    }

    public function test_cannot_mark_someone_elses_notification_as_read(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser();
        $other->notify(new AppNotification('Hi', '/love-sharing/1'));
        $notification = $other->notifications()->first();

        $this->actingAs($user)
            ->post(route('notifications.read', $notification->id))
            ->assertStatus(404);

        $this->assertNull($notification->fresh()->read_at);
    }

    public function test_a_link_to_another_site_is_never_followed(): void
    {
        $user = $this->makeUser();

        foreach (['https://evil.example/phish', '//evil.example', '/\\evil.example'] as $badUrl) {
            $user->notify(new AppNotification('Hi', $badUrl));
        }

        foreach ($user->notifications as $notification) {
            $this->actingAs($user)
                ->post(route('notifications.read', $notification->id))
                ->assertRedirect(route('notifications.index'));
        }
    }

    public function test_mark_all_as_read_only_affects_the_signed_in_user(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser();
        $user->notify(new AppNotification('One', '/x'));
        $user->notify(new AppNotification('Two', '/x'));
        $other->notify(new AppNotification('Theirs', '/x'));

        $this->actingAs($user)
            ->post(route('notifications.read-all'))
            ->assertRedirect();

        $this->assertSame(0, $user->unreadNotifications()->count());
        $this->assertSame(1, $other->unreadNotifications()->count());
    }

    public function test_guest_cannot_use_notifications(): void
    {
        $this->get(route('notifications.index'))->assertRedirect(route('login'));
        $this->post(route('notifications.read-all'))->assertRedirect(route('login'));
    }
}