<?php

namespace Tests\Feature;

use App\Models\CounselingAppointment;
use App\Models\CounselingRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CounselingAppointmentPrivacyTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'is_active' => true,
        ]);
    }

    protected function makeAppointmentFor(User $student, User $counselor): CounselingAppointment
    {
        $request = CounselingRequest::create([
            'student_id' => $student->id,
            'full_name' => $student->name,
            'phone_number' => '0911000000',
            'email' => $student->email,
            'description' => 'Confidential counseling description.',
            'counseling_type' => 'online',
        ]);

        return CounselingAppointment::create([
            'counseling_request_id' => $request->id,
            'student_id' => $student->id,
            'counselor_id' => $counselor->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '10:00',
            'appointment_type' => 'online',
        ]);
    }

    public function test_main_admin_cannot_view_an_appointment(): void
    {
        $admin = $this->makeUser('main_admin');
        $counselor = $this->makeUser('counseling_leader');
        $student = $this->makeUser('member');
        $appointment = $this->makeAppointmentFor($student, $counselor);

        $this->actingAs($admin)
            ->get(route('counseling-appointments.show', $appointment))
            ->assertStatus(403);
    }

    public function test_love_sharing_leader_cannot_view_an_appointment(): void
    {
        $loveSharingLeader = $this->makeUser('love_sharing_leader');
        $counselor = $this->makeUser('counseling_leader');
        $student = $this->makeUser('member');
        $appointment = $this->makeAppointmentFor($student, $counselor);

        $this->actingAs($loveSharingLeader)
            ->get(route('counseling-appointments.show', $appointment))
            ->assertStatus(403);
    }

    public function test_student_can_view_their_own_appointment(): void
    {
        $counselor = $this->makeUser('counseling_leader');
        $student = $this->makeUser('member');
        $appointment = $this->makeAppointmentFor($student, $counselor);

        $this->actingAs($student)
            ->get(route('counseling-appointments.show', $appointment))
            ->assertStatus(200);
    }

    public function test_student_cannot_view_another_students_appointment(): void
    {
        $counselor = $this->makeUser('counseling_leader');
        $studentA = $this->makeUser('member');
        $studentB = $this->makeUser('member');
        $appointment = $this->makeAppointmentFor($studentA, $counselor);

        $this->actingAs($studentB)
            ->get(route('counseling-appointments.show', $appointment))
            ->assertStatus(403);
    }

    public function test_counseling_leader_can_view_any_appointment(): void
    {
        $counselor = $this->makeUser('counseling_leader');
        $student = $this->makeUser('member');
        $appointment = $this->makeAppointmentFor($student, $counselor);

        $this->actingAs($counselor)
            ->get(route('counseling-appointments.show', $appointment))
            ->assertStatus(200);
    }

    public function test_student_cannot_create_an_appointment(): void
    {
        $student = $this->makeUser('member');

        $this->actingAs($student)
            ->get(route('counseling-appointments.create'))
            ->assertStatus(403);
    }

    public function test_counseling_leader_can_access_create_form(): void
    {
        $counselor = $this->makeUser('counseling_leader');

        $this->actingAs($counselor)
            ->get(route('counseling-appointments.create'))
            ->assertStatus(200);
    }

    public function test_guest_cannot_view_an_appointment(): void
    {
        $counselor = $this->makeUser('counseling_leader');
        $student = $this->makeUser('member');
        $appointment = $this->makeAppointmentFor($student, $counselor);

        $this->get(route('counseling-appointments.show', $appointment))
            ->assertRedirect(route('login'));
    }
}