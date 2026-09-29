<?php

namespace Tests\Feature;

use App\Models\CoworkerApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoworkerApplicationAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'is_active' => true,
        ]);
    }

    protected function applicationData(): array
    {
        return [
            'full_name' => 'Jane Doe',
            'phone_number' => '0911000000',
            'email' => 'jane@example.test',
            'reason_for_joining' => 'I want to serve the fellowship.',
        ];
    }

    public function test_guest_can_view_the_application_form(): void
    {
        $this->get(route('coworker-applications.create'))
            ->assertStatus(200);
    }

    public function test_guest_can_submit_an_application(): void
    {
        $this->post(route('coworker-applications.store'), $this->applicationData())
            ->assertRedirect(route('coworker-applications.thank-you'));

        $this->assertDatabaseHas('coworker_applications', [
            'email' => 'jane@example.test',
            'user_id' => null,
        ]);
    }

    public function test_logged_in_student_application_is_linked_to_their_account(): void
    {
        $student = $this->makeUser('member');

        $this->actingAs($student)
            ->post(route('coworker-applications.store'), $this->applicationData());

        $this->assertDatabaseHas('coworker_applications', [
            'email' => 'jane@example.test',
            'user_id' => $student->id,
        ]);
    }

    public function test_main_admin_can_view_applications_index(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->get(route('coworker-applications.index'))
            ->assertStatus(200);
    }

    public function test_member_cannot_view_applications_index(): void
    {
        $member = $this->makeUser('member');

        $this->actingAs($member)
            ->get(route('coworker-applications.index'))
            ->assertStatus(403);
    }

    public function test_love_sharing_leader_cannot_view_applications_index(): void
    {
        $leader = $this->makeUser('love_sharing_leader');

        $this->actingAs($leader)
            ->get(route('coworker-applications.index'))
            ->assertStatus(403);
    }

    public function test_guest_cannot_view_applications_index(): void
    {
        $this->get(route('coworker-applications.index'))
            ->assertRedirect(route('login'));
    }

    public function test_main_admin_can_update_application_status(): void
    {
        $admin = $this->makeUser('main_admin');
        $application = CoworkerApplication::create($this->applicationData());

        $this->actingAs($admin)
            ->put(route('coworker-applications.update', $application), ['status' => 'accepted'])
            ->assertRedirect();

        $this->assertDatabaseHas('coworker_applications', [
            'id' => $application->id,
            'status' => 'accepted',
            'reviewed_by' => $admin->id,
        ]);
    }
}