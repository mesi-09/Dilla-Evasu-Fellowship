<?php

namespace Tests\Feature;

use App\Models\CounselingAppointment;
use App\Models\CounselingRequest;
use App\Models\LoveSharingRequest;
use App\Models\User;
use App\Services\NotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected NotificationService $notifications;

    protected function setUp(): void
    {
        parent::setUp();
        $this->notifications = new NotificationService();
    }

    protected function makeUser(string $role, bool $active = true): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => $active]);
    }

    protected function loveSharingRequest(User $student, string $description = 'A private description.'): LoveSharingRequest
    {
        return LoveSharingRequest::create([
            'student_id' => $student->id,
            'full_name' => $student->name,
            'phone_number' => '0911000000',
            'email' => $student->email,
            'description' => $description,
        ]);
    }

    protected function counselingRequest(User $student, string $description = 'A private description.'): CounselingRequest
    {
        return CounselingRequest::create([
            'student_id' => $student->id,
            'full_name' => $student->name,
            'phone_number' => '0911000000',
            'email' => $student->email,
            'description' => $description,
            'counseling_type' => 'online',
        ]);
    }

    // ---- Who gets told ----

    public function test_new_love_sharing_request_notifies_the_student_and_the_love_sharing_leader(): void
    {
        $student = $this->makeUser('member');
        $leader = $this->makeUser('love_sharing_leader');

        $this->notifications->requestSubmitted($this->loveSharingRequest($student));

        $this->assertCount(1, $student->notifications);
        $this->assertCount(1, $leader->notifications);
    }

    public function test_main_admin_is_never_notified_about_a_private_request(): void
    {
        $student = $this->makeUser('member');
        $admin = $this->makeUser('main_admin');

        $this->notifications->requestSubmitted($this->loveSharingRequest($student));
        $this->notifications->requestSubmitted($this->counselingRequest($student));

        $this->assertCount(0, $admin->notifications);
    }

    public function test_the_other_teams_leader_is_never_notified(): void
    {
        $student = $this->makeUser('member');
        $loveSharingLeader = $this->makeUser('love_sharing_leader');
        $counselingLeader = $this->makeUser('counseling_leader');

        $this->notifications->requestSubmitted($this->counselingRequest($student));

        $this->assertCount(1, $counselingLeader->notifications);
        $this->assertCount(0, $loveSharingLeader->notifications);
    }

    public function test_inactive_leaders_are_not_notified(): void
    {
        $student = $this->makeUser('member');
        $inactive = $this->makeUser('love_sharing_leader', active: false);

        $this->notifications->requestSubmitted($this->loveSharingRequest($student));

        $this->assertCount(0, $inactive->notifications);
    }

    public function test_status_change_notifies_only_the_student(): void
    {
        $student = $this->makeUser('member');
        $leader = $this->makeUser('love_sharing_leader');
        $request = $this->loveSharingRequest($student);
        $request->update(['status' => 'accepted']);

        $this->notifications->statusChanged($request);

        $this->assertCount(1, $student->notifications);
        $this->assertCount(0, $leader->notifications);
        $this->assertStringContainsString('Accepted', $student->notifications->first()->data['message']);
    }

    public function test_a_message_from_the_student_notifies_the_leader_not_the_student(): void
    {
        $student = $this->makeUser('member');
        $leader = $this->makeUser('counseling_leader');
        $request = $this->counselingRequest($student);

        $this->notifications->newMessage($request, $student);

        $this->assertCount(1, $leader->notifications);
        $this->assertCount(0, $student->notifications);
    }

    public function test_a_message_from_the_leader_notifies_the_student(): void
    {
        $student = $this->makeUser('member');
        $leader = $this->makeUser('counseling_leader');
        $request = $this->counselingRequest($student);

        $this->notifications->newMessage($request, $leader);

        $this->assertCount(1, $student->notifications);
        $this->assertCount(0, $leader->notifications);
    }

    public function test_appointment_notifies_the_student(): void
    {
        $student = $this->makeUser('member');
        $counselor = $this->makeUser('counseling_leader');
        $request = $this->counselingRequest($student);

        $appointment = CounselingAppointment::create([
            'counseling_request_id' => $request->id,
            'student_id' => $student->id,
            'counselor_id' => $counselor->id,
            'appointment_date' => today()->addDay()->toDateString(),
            'appointment_time' => '10:00',
            'appointment_type' => 'online',
        ]);

        $this->notifications->appointmentScheduled($appointment);

        $this->assertCount(1, $student->notifications);
        $this->assertCount(0, $counselor->notifications);
    }

    // ---- What they say ----

    public function test_notifications_never_contain_the_private_description(): void
    {
        $student = $this->makeUser('member');
        $leader = $this->makeUser('love_sharing_leader');
        $request = $this->loveSharingRequest($student, 'SECRET-DESCRIPTION-TEXT');
        $request->update(['status' => 'accepted']);

        $this->notifications->requestSubmitted($request);
        $this->notifications->statusChanged($request);
        $this->notifications->newMessage($request, $student);

        $everything = $student->notifications->merge($leader->notifications)
            ->map(fn ($n) => json_encode($n->data))
            ->implode(' ');

        $this->assertNotEmpty($everything);
        $this->assertStringNotContainsString('SECRET-DESCRIPTION-TEXT', $everything);
        $this->assertStringNotContainsString($student->email, $everything);
        $this->assertStringNotContainsString('0911000000', $everything);
    }

    public function test_the_link_is_a_relative_path(): void
    {
        $student = $this->makeUser('member');
        $request = $this->loveSharingRequest($student);

        $this->notifications->statusChanged($request);

        $this->assertSame("/love-sharing/{$request->id}", $student->notifications->first()->data['url']);
    }
}