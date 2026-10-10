<?php

namespace Tests\Feature;

use App\Models\CounselingRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CounselingNotificationWiringTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    protected function counselingRequest(User $student): CounselingRequest
    {
        return CounselingRequest::create([
            'student_id' => $student->id,
            'full_name' => $student->name,
            'phone_number' => '0911000000',
            'email' => $student->email,
            'description' => 'A private description.',
            'counseling_type' => 'online',
        ]);
    }

    public function test_submitting_a_counseling_request_notifies_only_the_right_people(): void
    {
        $student = $this->makeUser('member');
        $counselingLeader = $this->makeUser('counseling_leader');
        $loveSharingLeader = $this->makeUser('love_sharing_leader');
        $admin = $this->makeUser('main_admin');

        $this->actingAs($student)->post(route('counseling.store'), [
            'full_name' => 'Test Student',
            'phone_number' => '0911000000',
            'email' => 'student@example.test',
            'description' => 'I would like to talk to someone.',
            'counseling_type' => 'online',
        ])->assertRedirect();

        $this->assertSame(1, $student->notifications()->count());
        $this->assertSame(1, $counselingLeader->notifications()->count());
        $this->assertSame(0, $loveSharingLeader->notifications()->count());
        $this->assertSame(0, $admin->notifications()->count());
    }

    public function test_changing_the_status_notifies_the_student(): void
    {
        $student = $this->makeUser('member');
        $leader = $this->makeUser('counseling_leader');
        $request = $this->counselingRequest($student);

        $this->actingAs($leader)
            ->put(route('counseling.update', $request), ['status' => 'accepted'])
            ->assertRedirect();

        $this->assertSame(1, $student->notifications()->count());
        $this->assertStringContainsString('Accepted', $student->notifications()->first()->data['message']);
    }

    public function test_a_student_message_notifies_the_counseling_leader_only(): void
    {
        $student = $this->makeUser('member');
        $counselingLeader = $this->makeUser('counseling_leader');
        $loveSharingLeader = $this->makeUser('love_sharing_leader');
        $request = $this->counselingRequest($student);

        $this->actingAs($student)
            ->post(route('counseling.messages.store', $request), ['body' => 'Hello'])
            ->assertRedirect();

        $this->assertSame(1, $counselingLeader->notifications()->count());
        $this->assertSame(0, $loveSharingLeader->notifications()->count());
    }

    public function test_a_leader_reply_notifies_the_student(): void
    {
        $student = $this->makeUser('member');
        $leader = $this->makeUser('counseling_leader');
        $request = $this->counselingRequest($student);

        $this->actingAs($leader)
            ->post(route('counseling.messages.store', $request), ['body' => 'I am here to help.'])
            ->assertRedirect();

        $this->assertSame(1, $student->notifications()->count());
        $this->assertSame(0, $leader->notifications()->count());
    }

    public function test_scheduling_an_appointment_notifies_the_student(): void
    {
        $student = $this->makeUser('member');
        $leader = $this->makeUser('counseling_leader');
        $request = $this->counselingRequest($student);

        $this->actingAs($leader)->post(route('counseling-appointments.store'), [
            'counseling_request_id' => $request->id,
            'student_id' => $student->id,
            'appointment_date' => today()->addDay()->toDateString(),
            'appointment_time' => '10:00',
            'appointment_type' => 'online',
        ])->assertRedirect();

        $this->assertSame(1, $student->notifications()->count());
        $this->assertSame(0, $leader->notifications()->count());
    }
}