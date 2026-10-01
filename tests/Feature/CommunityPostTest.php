<?php

namespace Tests\Feature;

use App\Models\CommunityPost;
use App\Models\ProhibitedWord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityPostTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'is_active' => true,
        ]);
    }

    public function test_member_can_post_a_safe_message(): void
    {
        $member = $this->makeUser('member');

        $this->actingAs($member)
            ->post(route('community.store'), ['body' => 'Does anyone know what time Bible study starts?'])
            ->assertRedirect();

        $this->assertDatabaseHas('community_posts', [
            'user_id' => $member->id,
            'moderation_status' => 'safe',
        ]);
    }

    public function test_prohibited_word_blocks_the_post(): void
    {
        ProhibitedWord::create(['word' => 'badword', 'category' => 'profanity', 'is_active' => true]);
        $member = $this->makeUser('member');

        $this->actingAs($member)
            ->post(route('community.store'), ['body' => 'That was a badword thing to say.'])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseMissing('community_posts', [
            'user_id' => $member->id,
        ]);
    }

    public function test_feed_only_shows_safe_posts(): void
    {
        $member = $this->makeUser('member');
        $safePost = CommunityPost::create(['user_id' => $member->id, 'body' => 'A safe post', 'moderation_status' => 'safe']);
        $blockedPost = CommunityPost::create(['user_id' => $member->id, 'body' => 'A blocked post', 'moderation_status' => 'blocked']);

        $this->actingAs($member)
            ->get(route('community.index'))
            ->assertSee('A safe post')
            ->assertDontSee('A blocked post');
    }

    public function test_author_can_delete_their_own_post(): void
    {
        $member = $this->makeUser('member');
        $post = CommunityPost::create(['user_id' => $member->id, 'body' => 'My post', 'moderation_status' => 'safe']);

        $this->actingAs($member)
            ->delete(route('community.destroy', $post))
            ->assertRedirect();

        $this->assertDatabaseMissing('community_posts', ['id' => $post->id]);
    }

    public function test_other_member_cannot_delete_someone_elses_post(): void
    {
        $author = $this->makeUser('member');
        $otherMember = $this->makeUser('member');
        $post = CommunityPost::create(['user_id' => $author->id, 'body' => 'My post', 'moderation_status' => 'safe']);

        $this->actingAs($otherMember)
            ->delete(route('community.destroy', $post))
            ->assertStatus(403);
    }

    public function test_main_admin_can_delete_any_post(): void
    {
        $admin = $this->makeUser('main_admin');
        $author = $this->makeUser('member');
        $post = CommunityPost::create(['user_id' => $author->id, 'body' => 'My post', 'moderation_status' => 'safe']);

        $this->actingAs($admin)
            ->delete(route('community.destroy', $post))
            ->assertRedirect();

        $this->assertDatabaseMissing('community_posts', ['id' => $post->id]);
    }

    public function test_guest_cannot_view_community_feed(): void
    {
        $this->get(route('community.index'))
            ->assertRedirect(route('login'));
    }
}