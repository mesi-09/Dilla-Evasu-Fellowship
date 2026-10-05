<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role, string $team = 'none'): User
    {
        return User::factory()->create([
            'role' => $role,
            'is_active' => true,
            'team' => $team,
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

    public function test_dashboard_shows_accurate_team_breakdown(): void
    {
        $admin = $this->makeUser('main_admin');
        $this->makeUser('member', 'worship');
        $this->makeUser('member', 'worship');
        $this->makeUser('member', 'choir');

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertStatus(200)
            ->assertViewHas('stats', function ($stats) {
                return $stats['by_team']['worship'] === 2
                    && $stats['by_team']['choir'] === 1
                    && $stats['by_team']['prayer'] === 0;
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

    public function test_member_list_can_be_filtered_by_team(): void
    {
        $admin = $this->makeUser('main_admin');
        User::factory()->create(['name' => 'Worship Member', 'role' => 'member', 'is_active' => true, 'team' => 'worship']);
        User::factory()->create(['name' => 'Choir Member', 'role' => 'member', 'is_active' => true, 'team' => 'choir']);

        $this->actingAs($admin)
            ->get(route('admin.members', ['team' => 'worship']))
            ->assertStatus(200)
            ->assertSee('Worship Member')
            ->assertDontSee('Choir Member');
    }

    public function test_guest_cannot_view_dashboard(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }
}