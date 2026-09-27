<?php

namespace Tests\Feature;

use App\Models\CounselingRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CounselingMessagePrivacyTest extends TestCase
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

    public function test_main_admin_cannot_view_the_message_thread(): void
    {
        $admin = $this->makeUser('main_admin');
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->actingAs($admin)
            ->get(route('counseling.messages.index', $request))
            ->assertStatus(403);
    }

    public function test_love_sharing_leader_cannot_view_the_message_thread(): void
    {
        $loveSharingLeader = $this->makeUser('love_sharing_leader');
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->actingAs($loveSharingLeader)
            ->get(route('counseling.messages.index', $request))
            ->assertStatus(403);
    }

    public function test_student_can_view_and_post_to_their_own_thread(): void
    {
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->actingAs($student)
            ->get(route('counseling.messages.index', $request))
            ->assertStatus(200);

        $this->actingAs($student)
            ->post(route('counseling.messages.store', $request), ['body' => 'I need to talk to someone.'])
            ->assertRedirect(route('counseling.messages.index', $request));

        $this->assertDatabaseHas('counseling_messages', [
            'counseling_request_id' => $request->id,
            'sender_id' => $student->id,
            'body' => 'I need to talk to someone.',
        ]);
    }

    public function test_student_cannot_view_another_students_thread(): void
    {
        $studentA = $this->makeUser('member');
        $studentB = $this->makeUser('member');
        $request = $this->makeRequestFor($studentA);

        $this->actingAs($studentB)
            ->get(route('counseling.messages.index', $request))
            ->assertStatus(403);
    }

    public function test_counseling_leader_can_view_and_reply_to_any_thread(): void
    {
        $leader = $this->makeUser('counseling_leader');
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->actingAs($leader)
            ->get(route('counseling.messages.index', $request))
            ->assertStatus(200);

        $this->actingAs($leader)
            ->post(route('counseling.messages.store', $request), ['body' => 'I am here to help.'])
            ->assertRedirect(route('counseling.messages.index', $request));
    }

    public function test_guest_cannot_view_a_thread(): void
    {
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->get(route('counseling.messages.index', $request))
            ->assertRedirect(route('login'));
    }
}