<?php

namespace Tests\Feature;

use App\Models\CounselingAppointment;
use App\Models\CounselingRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentChangeNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    protected function makeAppointment(User $student, User $counselor): CounselingAppointment
    {
        $request = CounselingRequest::create([
            'student_id' => $student->id,
            'full_name' => $student->name,
            'phone_number' => '0911000000',
            'email' => $student->email,
            'description' => 'A private description.',
            'counseling_type' => 'online',
        ]);

        return CounselingAppointment::create([
            'counseling_request_id' => $request->id,
            'student_id' => $student->id,
            'counselor_id' => $counselor->id,
            'appointment_date' => today()->addDay()->toDateString(),
            'appointment_time' => '10:00',
            'appointment_type' => 'online',
            'status' => 'scheduled',
        ]);
    }

    protected function payload(CounselingAppointment $appointment, array $overrides = []): array
    {
        return array_merge([
            'status' => 'scheduled',
            'appointment_date' => today()->addDay()->toDateString(),
            'appointment_time' => '10:00',
        ], $overrides);
    }

    public function test_changing_the_status_notifies_the_student(): void
    {
        $student = $this->makeUser('member');
        $counselor = $this->makeUser('counseling_leader');
        $appointment = $this->makeAppointment($student, $counselor);

        $this->actingAs($counselor)
            ->put(route('counseling-appointments.update', $appointment), $this->payload($appointment, ['status' => 'cancelled']))
            ->assertRedirect();

        $this->assertSame(1, $student->notifications()->count());
        $this->assertStringContainsString('Cancelled', $student->notifications()->first()->data['message']);
    }

    public function test_changing_the_date_notifies_the_student(): void
    {
        $student = $this->makeUser('member');
        $counselor = $this->makeUser('counseling_leader');
        $appointment = $this->makeAppointment($student, $counselor);

        $this->actingAs($counselor)
            ->put(route('counseling-appointments.update', $appointment), $this->payload($appointment, [
                'appointment_date' => today()->addDays(4)->toDateString(),
            ]))
            ->assertRedirect();

        $this->assertSame(1, $student->notifications()->count());
        $this->assertStringContainsString('date or time', $student->notifications()->first()->data['message']);
    }

    public function test_changing_the_time_notifies_the_student(): void
    {
        $student = $this->makeUser('member');
        $counselor = $this->makeUser('counseling_leader');
        $appointment = $this->makeAppointment($student, $counselor);

        $this->actingAs($counselor)
            ->put(route('counseling-appointments.update', $appointment), $this->payload($appointment, [
                'appointment_time' => '14:30',
            ]))
            ->assertRedirect();

        $this->assertSame(1, $student->notifications()->count());
    }

    public function test_saving_the_same_values_again_does_not_notify(): void
    {
        $student = $this->makeUser('member');
        $counselor = $this->makeUser('counseling_leader');
        $appointment = $this->makeAppointment($student, $counselor);

        $this->actingAs($counselor)
            ->put(route('counseling-appointments.update', $appointment), $this->payload($appointment))
            ->assertRedirect();

        $this->assertSame(0, $student->notifications()->count());
    }

    public function test_editing_only_the_private_notes_does_not_notify_and_never_leaks_them(): void
    {
        $student = $this->makeUser('member');
        $counselor = $this->makeUser('counseling_leader');
        $appointment = $this->makeAppointment($student, $counselor);

        $this->actingAs($counselor)
            ->put(route('counseling-appointments.update', $appointment), $this->payload($appointment, [
                'notes' => 'SECRET-COUNSELOR-NOTE',
            ]))
            ->assertRedirect();

        $this->assertSame(0, $student->notifications()->count());
        $this->assertSame('SECRET-COUNSELOR-NOTE', $appointment->fresh()->notes);
    }

    public function test_only_the_student_is_notified(): void
    {
        $student = $this->makeUser('member');
        $counselor = $this->makeUser('counseling_leader');
        $loveSharingLeader = $this->makeUser('love_sharing_leader');
        $admin = $this->makeUser('main_admin');
        $appointment = $this->makeAppointment($student, $counselor);

        $this->actingAs($counselor)
            ->put(route('counseling-appointments.update', $appointment), $this->payload($appointment, ['status' => 'completed']));

        $this->assertSame(1, $student->notifications()->count());
        $this->assertSame(0, $counselor->notifications()->count());
        $this->assertSame(0, $loveSharingLeader->notifications()->count());
        $this->assertSame(0, $admin->notifications()->count());
    }

    public function test_a_student_cannot_change_their_own_appointment(): void
    {
        $student = $this->makeUser('member');
        $counselor = $this->makeUser('counseling_leader');
        $appointment = $this->makeAppointment($student, $counselor);

        $this->actingAs($student)
            ->put(route('counseling-appointments.update', $appointment), $this->payload($appointment, ['status' => 'cancelled']))
            ->assertStatus(403);

        $this->assertSame('scheduled', $appointment->fresh()->status);
        $this->assertSame(0, $student->notifications()->count());
        $this->assertSame(0, $counselor->notifications()->count());
    }
}