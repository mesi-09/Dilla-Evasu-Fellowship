<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationRateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function validData(string $email): array
    {
        return [
            'name' => 'Test User',
            'email' => $email,
            'team' => 'worship',
            'password' => 'password',
            'password_confirmation' => 'password',
        ];
    }

    /**
     * Attempts that fail validation still count towards the limit. They are
     * also cheap (no password hashing, and nobody ends up logged in), which
     * makes them a convenient way to use up the allowance.
     */
    protected function useUpTheAllowance(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->post('/register', ['name' => 'x'])->assertSessionHasErrors('email');
        }
    }

    public function test_registration_is_limited_per_address_and_a_blocked_attempt_creates_no_account(): void
    {
        $this->useUpTheAllowance();

        $this->post('/register', $this->validData('late@example.test'))
            ->assertStatus(429)
            ->assertSee('Please wait a moment');

        $this->assertSame(0, User::where('email', 'late@example.test')->count());
        $this->assertGuest();
    }

    public function test_another_address_can_still_register(): void
    {
        $this->useUpTheAllowance();

        $this->withServerVariables(['REMOTE_ADDR' => '10.20.30.40'])
            ->post('/register', $this->validData('other@example.test'))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('users', ['email' => 'other@example.test']);
    }

    public function test_viewing_the_registration_page_is_never_limited(): void
    {
        for ($i = 0; $i < 25; $i++) {
            $this->get('/register')->assertStatus(200);
        }
    }
}