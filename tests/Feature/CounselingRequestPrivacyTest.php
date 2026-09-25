<?php

namespace Tests\Feature;

use App\Models\CounselingRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CounselingRequestPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'is_active' => true,
        ]);
    }

    protected function makeRequestFor(User $student): CounselingRequest
    {
        return CounselingRequest::create([
            'student_id' => $student->id,
            'full_name' => $student->name,
            'phone_number' => '0911000000',
            'email' => $student->email,
            'description' => 'Confidential counseling description.',
            'counseling_type' => 'online',
        ]);
    }

    public function test_main_admin_cannot_view_a_counseling_request(): void
    {
        $admin = $this->makeUser('main_admin');
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->actingAs($admin)
            ->get(route('counseling.show', $request))
            ->assertStatus(403);
    }

    public function test_main_admin_cannot_view_the_counseling_index(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->get(route('counseling.index'))
            ->assertStatus(403);
    }

    public function test_love_sharing_leader_cannot_view_a_counseling_request(): void
    {
        $loveSharingLeader = $this->makeUser('love_sharing_leader');
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->actingAs($loveSharingLeader)
            ->get(route('counseling.show', $request))
            ->assertStatus(403);
    }

    public function test_love_sharing_leader_cannot_view_the_counseling_index(): void
    {
        $loveSharingLeader = $this->makeUser('love_sharing_leader');

        $this->actingAs($loveSharingLeader)
            ->get(route('counseling.index'))
            ->assertStatus(403);
    }

    public function test_student_can_view_their_own_request(): void
    {
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->actingAs($student)
            ->get(route('counseling.show', $request))
            ->assertStatus(200)
            ->assertSee($request->description);
    }

    public function test_student_cannot_view_another_students_request(): void
    {
        $studentA = $this->makeUser('member');
        $studentB = $this->makeUser('member');
        $request = $this->makeRequestFor($studentA);

        $this->actingAs($studentB)
            ->get(route('counseling.show', $request))
            ->assertStatus(403);
    }

    public function test_counseling_leader_can_view_any_request(): void
    {
        $leader = $this->makeUser('counseling_leader');
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->actingAs($leader)
            ->get(route('counseling.show', $request))
            ->assertStatus(200);
    }

    public function test_guest_cannot_view_a_counseling_request(): void
    {
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->get(route('counseling.show', $request))
            ->assertRedirect(route('login'));
    }
}