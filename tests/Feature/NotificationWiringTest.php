<?php

namespace Tests\Feature;

use App\Models\LoveSharingRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationWiringTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
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

    // ---- Love Sharing ----

    public function test_submitting_a_love_sharing_request_notifies_the_student_and_leader(): void
    {
        $student = $this->makeUser('member');
        $leader = $this->makeUser('love_sharing_leader');
        $admin = $this->makeUser('main_admin');

        $this->actingAs($student)->post(route('love-sharing.store'), [
            'full_name' => 'Test Student',
            'phone_number' => '0911000000',
            'email' => 'student@example.test',
            'description' => 'I need someone to talk to.',
        ])->assertRedirect();

        $this->assertSame(1, $student->notifications()->count());
        $this->assertSame(1, $leader->notifications()->count());
        $this->assertSame(0, $admin->notifications()->count());
    }

    public function test_changing_the_status_notifies_the_student(): void
    {
        $student = $this->makeUser('member');
        $leader = $this->makeUser('love_sharing_leader');
        $request = $this->loveSharingRequest($student);

        $this->actingAs($leader)
            ->put(route('love-sharing.update', $request), ['status' => 'accepted'])
            ->assertRedirect();

        $this->assertSame(1, $student->notifications()->count());
        $this->assertStringContainsString('Accepted', $student->notifications()->first()->data['message']);
        $this->assertSame(0, $leader->notifications()->count());
    }

    public function test_saving_the_same_status_again_does_not_notify(): void
    {
        $student = $this->makeUser('member');
        $leader = $this->makeUser('love_sharing_leader');
        $request = $this->loveSharingRequest($student); // starts as 'pending'

        $this->actingAs($leader)
            ->put(route('love-sharing.update', $request), ['status' => 'pending']);

        $this->assertSame(0, $student->notifications()->count());
    }

    public function test_a_student_message_notifies_the_leader(): void
    {
        $student = $this->makeUser('member');
        $leader = $this->makeUser('love_sharing_leader');
        $request = $this->loveSharingRequest($student);

        $this->actingAs($student)
            ->post(route('love-sharing.messages.store', $request), ['body' => 'Hello'])
            ->assertRedirect();

        $this->assertSame(1, $leader->notifications()->count());
        $this->assertSame(0, $student->notifications()->count());
    }

    public function test_a_leader_reply_notifies_the_student(): void
    {
        $student = $this->makeUser('member');
        $leader = $this->makeUser('love_sharing_leader');
        $request = $this->loveSharingRequest($student);

        $this->actingAs($leader)
            ->post(route('love-sharing.messages.store', $request), ['body' => 'How can I help?'])
            ->assertRedirect();

        $this->assertSame(1, $student->notifications()->count());
        $this->assertSame(0, $leader->notifications()->count());
    }

    public function test_a_blocked_message_sends_no_notification(): void
    {
        $student = $this->makeUser('member');
        $leader = $this->makeUser('love_sharing_leader');
        $otherStudent = $this->makeUser('member');
        $request = $this->loveSharingRequest($student);

        // Not their thread, so the Policy refuses it. Nobody should hear about it.
        $this->actingAs($otherStudent)
            ->post(route('love-sharing.messages.store', $request), ['body' => 'Hello'])
            ->assertStatus(403);

        $this->assertSame(0, $leader->notifications()->count());
        $this->assertSame(0, $student->notifications()->count());
    }
}