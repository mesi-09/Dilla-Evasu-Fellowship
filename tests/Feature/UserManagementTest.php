<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\LoveSharingRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role, array $attributes = []): User
    {
        return User::factory()->create(array_merge(['role' => $role, 'is_active' => true], $attributes));
    }

    protected function loveSharingRequest(User $student): LoveSharingRequest
    {
        return LoveSharingRequest::create([
            'student_id' => $student->id,
            'full_name' => $student->name,
            'phone_number' => '0911000000',
            'email' => $student->email,
            'description' => 'A private description.',
        ]);
    }

    public function test_admin_can_change_another_users_role_and_it_is_logged(): void
    {
        $admin = $this->makeUser('main_admin');
        $member = $this->makeUser('member', ['name' => 'Test Member']);

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $member), ['role' => 'counseling_leader'])
            ->assertRedirect();

        $this->assertSame('counseling_leader', $member->fresh()->role);

        $log = AuditLog::first();
        $this->assertSame('user.role_changed', $log->action);
        $this->assertSame($admin->id, $log->user_id);
        $this->assertStringContainsString('Test Member', $log->description);
        $this->assertStringContainsString('from Member to Counseling leader', $log->description);
        $this->assertStringNotContainsString($member->email, $log->description);
    }

    public function test_an_unknown_role_is_rejected(): void
    {
        $admin = $this->makeUser('main_admin');
        $member = $this->makeUser('member');

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $member), ['role' => 'super_admin'])
            ->assertSessionHasErrors('role');

        $this->assertSame('member', $member->fresh()->role);
        $this->assertSame(0, AuditLog::count());
    }

    public function test_an_admin_cannot_change_their_own_role(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $admin), ['role' => 'love_sharing_leader'])
            ->assertStatus(403);

        $this->assertSame('main_admin', $admin->fresh()->role);
        $this->assertSame(0, AuditLog::count());
    }

    public function test_setting_the_same_role_again_is_not_logged(): void
    {
        $admin = $this->makeUser('main_admin');
        $member = $this->makeUser('member');

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $member), ['role' => 'member'])
            ->assertRedirect();

        $this->assertSame(0, AuditLog::count());
    }

    public function test_admin_can_deactivate_a_user_and_it_is_logged(): void
    {
        $admin = $this->makeUser('main_admin');
        $member = $this->makeUser('member', ['name' => 'Test Member']);

        $this->actingAs($admin)
            ->patch(route('admin.users.toggle-active', $member))
            ->assertRedirect();

        $this->assertFalse((bool) $member->fresh()->is_active);

        $log = AuditLog::first();
        $this->assertSame('user.deactivated', $log->action);
        $this->assertStringContainsString('Test Member', $log->description);
    }

    public function test_admin_can_reactivate_a_user_and_it_is_logged(): void
    {
        $admin = $this->makeUser('main_admin');
        $member = $this->makeUser('member', ['is_active' => false]);

        $this->actingAs($admin)
            ->patch(route('admin.users.toggle-active', $member))
            ->assertRedirect();

        $this->assertTrue((bool) $member->fresh()->is_active);
        $this->assertSame('user.activated', AuditLog::first()->action);
    }

    public function test_an_admin_cannot_deactivate_themselves(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->patch(route('admin.users.toggle-active', $admin))
            ->assertStatus(403);

        $this->assertTrue((bool) $admin->fresh()->is_active);
        $this->assertSame(0, AuditLog::count());
    }

    public function test_nobody_but_an_admin_can_change_roles_or_deactivate(): void
    {
        $victim = $this->makeUser('member');

        foreach (['member', 'love_sharing_leader', 'counseling_leader'] as $role) {
            $actor = $this->makeUser($role);

            // Including promoting themselves to admin.
            $this->actingAs($actor)
                ->patch(route('admin.users.role', $actor), ['role' => 'main_admin'])
                ->assertStatus(403);

            $this->actingAs($actor)
                ->patch(route('admin.users.toggle-active', $victim))
                ->assertStatus(403);

            $this->assertSame($role, $actor->fresh()->role);
        }

        $this->assertTrue((bool) $victim->fresh()->is_active);
        $this->assertSame(0, AuditLog::count());
    }

    public function test_guests_are_sent_to_login(): void
    {
        $member = $this->makeUser('member');

        $this->patch(route('admin.users.role', $member), ['role' => 'main_admin'])
            ->assertRedirect(route('login'));
        $this->patch(route('admin.users.toggle-active', $member))
            ->assertRedirect(route('login'));
    }

    public function test_the_members_page_hides_the_controls_on_your_own_row(): void
    {
        $admin = $this->makeUser('main_admin');
        $other = $this->makeUser('member');

        $this->actingAs($admin)
            ->get(route('admin.members'))
            ->assertStatus(200)
            ->assertSee(route('admin.users.role', $other), false)
            ->assertDontSee(route('admin.users.role', $admin), false)
            ->assertSee('(you)');
    }

    public function test_promoting_a_member_to_leader_takes_effect(): void
    {
        $admin = $this->makeUser('main_admin');
        $student = $this->makeUser('member');
        $other = $this->makeUser('member');
        $request = $this->loveSharingRequest($student);

        $this->actingAs($other)
            ->get(route('love-sharing.show', $request))
            ->assertStatus(403);

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $other), ['role' => 'love_sharing_leader'])
            ->assertRedirect();

        $this->actingAs($other->fresh())
            ->get(route('love-sharing.show', $request))
            ->assertStatus(200);
    }

    public function test_demoting_a_leader_removes_their_access(): void
    {
        $admin = $this->makeUser('main_admin');
        $student = $this->makeUser('member');
        $leader = $this->makeUser('love_sharing_leader');
        $request = $this->loveSharingRequest($student);

        $this->actingAs($leader)
            ->get(route('love-sharing.show', $request))
            ->assertStatus(200);

        $this->actingAs($admin)
            ->patch(route('admin.users.role', $leader), ['role' => 'member'])
            ->assertRedirect();

        $this->actingAs($leader->fresh())
            ->get(route('love-sharing.show', $request))
            ->assertStatus(403);
    }
}