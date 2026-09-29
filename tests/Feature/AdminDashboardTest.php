<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'is_active' => true,
        ]);
    }

    public function test_main_admin_can_view_dashboard_with_accurate_member_count(): void
    {
        $admin = $this->makeUser('main_admin');
        $this->makeUser('member');
        $this->makeUser('member');
        $this->makeUser('love_sharing_leader');

        // 4 total: admin + 2 members + 1 leader
        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertStatus(200)
            ->assertSee('4') // total_members
            ->assertViewHas('stats', function ($stats) {
                return $stats['total_members'] === 4
                    && $stats['by_role']['member'] === 2
                    && $stats['by_role']['love_sharing_leader'] === 1;
            });
    }

    public function test_member_cannot_view_dashboard(): void
    {
        $member = $this->makeUser('member');

        $this->actingAs($member)
            ->get(route('admin.dashboard'))
            ->assertStatus(403);
    }

    public function test_main_admin_can_view_member_list(): void
    {
        $admin = $this->makeUser('main_admin');
        $this->makeUser('member');

        $this->actingAs($admin)
            ->get(route('admin.members'))
            ->assertStatus(200);
    }

    public function test_member_cannot_view_member_list(): void
    {
        $member = $this->makeUser('member');

        $this->actingAs($member)
            ->get(route('admin.members'))
            ->assertStatus(403);
    }

    public function test_member_list_can_be_searched_by_name(): void
    {
        $admin = $this->makeUser('main_admin');
        User::factory()->create(['name' => 'Findable Person', 'role' => 'member', 'is_active' => true]);
        User::factory()->create(['name' => 'Someone Else', 'role' => 'member', 'is_active' => true]);

        $this->actingAs($admin)
            ->get(route('admin.members', ['search' => 'Findable']))
            ->assertStatus(200)
            ->assertSee('Findable Person')
            ->assertDontSee('Someone Else');
    }

    public function test_guest_cannot_view_dashboard(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }
}