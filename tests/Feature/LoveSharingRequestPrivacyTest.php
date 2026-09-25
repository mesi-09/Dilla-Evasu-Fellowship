<?php

namespace Tests\Feature;

use App\Models\LoveSharingRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoveSharingRequestPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'is_active' => true,
        ]);
    }

    protected function makeRequestFor(User $student): LoveSharingRequest
    {
        return LoveSharingRequest::create([
            'student_id' => $student->id,
            'full_name' => $student->name,
            'phone_number' => '0911000000',
            'email' => $student->email,
            'description' => 'Confidential description.',
        ]);
    }

    public function test_main_admin_cannot_view_a_love_sharing_request(): void
    {
        $admin = $this->makeUser('main_admin');
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->actingAs($admin)
            ->get(route('love-sharing.show', $request))
            ->assertStatus(403);
    }

    public function test_main_admin_cannot_view_the_love_sharing_index(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->get(route('love-sharing.index'))
            ->assertStatus(403);
    }

    public function test_student_can_view_their_own_request(): void
    {
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->actingAs($student)
            ->get(route('love-sharing.show', $request))
            ->assertStatus(200)
            ->assertSee($request->description);
    }

    public function test_student_cannot_view_another_students_request(): void
    {
        $studentA = $this->makeUser('member');
        $studentB = $this->makeUser('member');
        $request = $this->makeRequestFor($studentA);

        $this->actingAs($studentB)
            ->get(route('love-sharing.show', $request))
            ->assertStatus(403);
    }

    public function test_love_sharing_leader_can_view_any_request(): void
    {
        $leader = $this->makeUser('love_sharing_leader');
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->actingAs($leader)
            ->get(route('love-sharing.show', $request))
            ->assertStatus(200);
    }

    public function test_counseling_leader_cannot_view_a_love_sharing_request(): void
    {
        $counselingLeader = $this->makeUser('counseling_leader');
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->actingAs($counselingLeader)
            ->get(route('love-sharing.show', $request))
            ->assertStatus(403);
    }

    public function test_guest_cannot_view_a_love_sharing_request(): void
    {
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->get(route('love-sharing.show', $request))
            ->assertRedirect(route('login'));
    }
}