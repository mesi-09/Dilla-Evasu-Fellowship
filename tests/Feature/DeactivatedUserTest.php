<?php

namespace Tests\Feature;

use App\Models\LoveSharingRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeactivatedUserTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_deactivated_user_is_logged_out_and_sent_to_login(): void
    {
        $user = User::factory()->create(['role' => 'member', 'is_active' => false]);

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_deactivated_user_is_logged_out_on_a_public_page_too(): void
    {
        $user = User::factory()->create(['role' => 'member', 'is_active' => false]);

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_a_deactivated_leader_can_no_longer_read_private_requests(): void
    {
        $student = User::factory()->create(['role' => 'member', 'is_active' => true]);
        $request = LoveSharingRequest::create([
            'student_id' => $student->id,
            'full_name' => $student->name,
            'phone_number' => '0911000000',
            'email' => $student->email,
            'description' => 'A private description.',
        ]);
        $leader = User::factory()->create(['role' => 'love_sharing_leader', 'is_active' => false]);

        $this->actingAs($leader)
            ->get(route('love-sharing.show', $request))
            ->assertRedirect(route('login'));
    }

    public function test_an_active_user_is_not_affected(): void
    {
        $user = User::factory()->create(['role' => 'member', 'is_active' => true]);

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertStatus(200);
    }

    public function test_a_user_with_no_explicit_status_is_treated_as_active(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertStatus(200);
    }

    public function test_guests_are_not_affected(): void
    {
        $this->get('/')->assertStatus(200);
    }
}