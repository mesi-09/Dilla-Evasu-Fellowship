<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationBellTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role = 'member'): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    public function test_bell_shows_the_unread_count_inside_the_app(): void
    {
        $user = $this->makeUser();
        $user->notify(new AppNotification('One', '/x'));
        $user->notify(new AppNotification('Two', '/x'));

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertStatus(200)
            ->assertSee('Notifications (2 unread)');
    }

    public function test_bell_shows_the_unread_count_on_the_public_homepage(): void
    {
        $user = $this->makeUser();
        $user->notify(new AppNotification('One', '/x'));

        $this->actingAs($user)
            ->get(route('home'))
            ->assertSee('Notifications (1 unread)');
    }

    public function test_no_badge_when_everything_is_read(): void
    {
        $user = $this->makeUser();
        $user->notify(new AppNotification('One', '/x'));
        $user->unreadNotifications->markAsRead();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertDontSee('unread)');
    }

    public function test_count_only_includes_my_own_notifications(): void
    {
        $user = $this->makeUser();
        $other = $this->makeUser();
        $other->notify(new AppNotification('Theirs', '/x'));
        $other->notify(new AppNotification('Theirs too', '/x'));

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertDontSee('unread)');
    }

    public function test_large_counts_are_capped(): void
    {
        $user = $this->makeUser();

        for ($i = 0; $i < 100; $i++) {
            $user->notify(new AppNotification('N'.$i, '/x'));
        }

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertSee('99+');
    }

    public function test_guests_do_not_see_a_bell(): void
    {
        $this->get(route('home'))
            ->assertStatus(200)
            ->assertDontSee('Notifications');
    }
}