<?php

namespace Tests\Feature;

use App\Models\CommunityPost;
use App\Models\CoworkerApplication;
use App\Models\LoveSharingRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role = 'member'): User
    {
        return User::factory()->create(['role' => $role, 'is_active' => true]);
    }

    protected function applicationData(): array
    {
        return [
            'full_name' => 'Jane Doe',
            'phone_number' => '0911000000',
            'email' => 'jane@example.test',
            'reason_for_joining' => 'I want to serve the fellowship.',
        ];
    }

    protected function requestData(): array
    {
        return [
            'full_name' => 'Test Student',
            'phone_number' => '0911000000',
            'email' => 'student@example.test',
            'description' => 'I need someone to talk to.',
        ];
    }

    protected function counselingData(): array
    {
        return array_merge($this->requestData(), ['counseling_type' => 'online']);
    }

    protected function post_(User $author): CommunityPost
    {
        return CommunityPost::create(['user_id' => $author->id, 'body' => 'A post', 'moderation_status' => 'safe']);
    }

    // ---- Public form (counted by address) ----

    public function test_the_public_co_worker_form_is_limited_per_address(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->post(route('coworker-applications.store'), $this->applicationData())
                ->assertRedirect(route('coworker-applications.thank-you'));
        }

        $this->post(route('coworker-applications.store'), $this->applicationData())
            ->assertStatus(429)
            ->assertSee('Please wait a moment');

        $this->assertSame(10, CoworkerApplication::count());
    }

    public function test_one_addresss_limit_does_not_block_another(): void
    {
        for ($i = 0; $i < 11; $i++) {
            $this->post(route('coworker-applications.store'), $this->applicationData());
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.20.30.40'])
            ->post(route('coworker-applications.store'), $this->applicationData())
            ->assertRedirect(route('coworker-applications.thank-you'));
    }

    // ---- Community (counted by account) ----

    public function test_community_posting_is_limited_per_user(): void
    {
        $user = $this->makeUser();

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($user)
                ->post(route('community.store'), ['body' => "Post {$i}"])
                ->assertRedirect();
        }

        $this->actingAs($user)
            ->post(route('community.store'), ['body' => 'One too many'])
            ->assertStatus(429);

        $this->assertSame(10, CommunityPost::count());
    }

    public function test_one_users_limit_does_not_block_another_user(): void
    {
        $busy = $this->makeUser();
        $other = $this->makeUser();

        for ($i = 0; $i < 11; $i++) {
            $this->actingAs($busy)->post(route('community.store'), ['body' => "Post {$i}"]);
        }

        $this->actingAs($other)
            ->post(route('community.store'), ['body' => 'Hello'])
            ->assertRedirect();
    }

    public function test_commenting_is_limited_per_user(): void
    {
        $author = $this->makeUser();
        $commenter = $this->makeUser();
        $post = $this->post_($author);

        for ($i = 0; $i < 10; $i++) {
            $this->actingAs($commenter)
                ->post(route('community.comments.store', $post), ['body' => "Comment {$i}"])
                ->assertRedirect();
        }

        $this->actingAs($commenter)
            ->post(route('community.comments.store', $post), ['body' => 'One too many'])
            ->assertStatus(429);
    }

    public function test_reacting_is_limited_per_user(): void
    {
        $author = $this->makeUser();
        $reactor = $this->makeUser();
        $post = $this->post_($author);

        for ($i = 0; $i < 60; $i++) {
            $this->actingAs($reactor)
                ->post(route('community.reactions.toggle', $post))
                ->assertRedirect();
        }

        $this->actingAs($reactor)
            ->post(route('community.reactions.toggle', $post))
            ->assertStatus(429);
    }

    // ---- Private requests and messages ----

    public function test_request_submissions_share_one_limit_across_both_teams(): void
    {
        $student = $this->makeUser();

        for ($i = 0; $i < 5; $i++) {
            $this->actingAs($student)->post(route('love-sharing.store'), $this->requestData())->assertRedirect();
            $this->actingAs($student)->post(route('counseling.store'), $this->counselingData())->assertRedirect();
        }

        $this->actingAs($student)->post(route('love-sharing.store'), $this->requestData())->assertStatus(429);
        $this->actingAs($student)->post(route('counseling.store'), $this->counselingData())->assertStatus(429);

        $this->assertSame(5, LoveSharingRequest::count());
    }

    public function test_private_messages_are_limited_per_user(): void
    {
        $student = $this->makeUser();
        $request = LoveSharingRequest::create([
            'student_id' => $student->id,
            'full_name' => $student->name,
            'phone_number' => '0911000000',
            'email' => $student->email,
            'description' => 'A private description.',
        ]);

        for ($i = 0; $i < 20; $i++) {
            $this->actingAs($student)
                ->post(route('love-sharing.messages.store', $request), ['body' => "Message {$i}"])
                ->assertRedirect();
        }

        $this->actingAs($student)
            ->post(route('love-sharing.messages.store', $request), ['body' => 'One too many'])
            ->assertStatus(429);
    }

    // ---- Normal use, and login ----

    public function test_a_normal_burst_of_activity_is_never_blocked(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->post(route('community.store'), ['body' => 'Hello everyone'])->assertRedirect();
        $post = CommunityPost::first();
        $this->actingAs($user)->post(route('community.comments.store', $post), ['body' => 'First comment'])->assertRedirect();
        $this->actingAs($user)->post(route('community.reactions.toggle', $post))->assertRedirect();
        $this->actingAs($user)->post(route('love-sharing.store'), $this->requestData())->assertRedirect();
        $this->actingAs($user)->post(route('counseling.store'), $this->counselingData())->assertRedirect();
    }

    public function test_repeated_failed_logins_are_locked_out(): void
    {
        $user = $this->makeUser();

        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
                ->assertSessionHasErrors('email');
        }

        // Once locked out, even the correct password is refused.
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertStringContainsString('Too many login attempts', session('errors')->first('email'));
    }
}