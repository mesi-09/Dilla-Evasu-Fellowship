<?php

namespace Tests\Feature;

use App\Models\LoveSharingRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoveSharingMessagePrivacyTest extends TestCase
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

    public function test_main_admin_cannot_view_the_message_thread(): void
    {
        $admin = $this->makeUser('main_admin');
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->actingAs($admin)
            ->get(route('love-sharing.messages.index', $request))
            ->assertStatus(403);
    }

    public function test_main_admin_cannot_post_a_message(): void
    {
        $admin = $this->makeUser('main_admin');
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->actingAs($admin)
            ->post(route('love-sharing.messages.store', $request), ['body' => 'Hello'])
            ->assertStatus(403);
    }

    public function test_student_can_view_and_post_to_their_own_thread(): void
    {
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->actingAs($student)
            ->get(route('love-sharing.messages.index', $request))
            ->assertStatus(200);

        $this->actingAs($student)
            ->post(route('love-sharing.messages.store', $request), ['body' => 'Hello, I need help.'])
            ->assertRedirect(route('love-sharing.messages.index', $request));

        $this->assertDatabaseHas('love_sharing_messages', [
            'love_sharing_request_id' => $request->id,
            'sender_id' => $student->id,
            'body' => 'Hello, I need help.',
        ]);
    }

    public function test_student_cannot_view_another_students_thread(): void
    {
        $studentA = $this->makeUser('member');
        $studentB = $this->makeUser('member');
        $request = $this->makeRequestFor($studentA);

        $this->actingAs($studentB)
            ->get(route('love-sharing.messages.index', $request))
            ->assertStatus(403);
    }

    public function test_love_sharing_leader_can_view_and_reply_to_any_thread(): void
    {
        $leader = $this->makeUser('love_sharing_leader');
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->actingAs($leader)
            ->get(route('love-sharing.messages.index', $request))
            ->assertStatus(200);

        $this->actingAs($leader)
            ->post(route('love-sharing.messages.store', $request), ['body' => 'How can I help?'])
            ->assertRedirect(route('love-sharing.messages.index', $request));
    }

    public function test_counseling_leader_cannot_view_a_love_sharing_thread(): void
    {
        $counselingLeader = $this->makeUser('counseling_leader');
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->actingAs($counselingLeader)
            ->get(route('love-sharing.messages.index', $request))
            ->assertStatus(403);
    }

    public function test_guest_cannot_view_a_thread(): void
    {
        $student = $this->makeUser('member');
        $request = $this->makeRequestFor($student);

        $this->get(route('love-sharing.messages.index', $request))
            ->assertRedirect(route('login'));
    }
}