<?php

namespace Tests\Feature;

use App\Models\CommunityComment;
use App\Models\CommunityPost;
use App\Models\ProhibitedWord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityCommentAndReactionTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'is_active' => true,
        ]);
    }

    protected function makePost(User $author): CommunityPost
    {
        return CommunityPost::create(['user_id' => $author->id, 'body' => 'A post', 'moderation_status' => 'safe']);
    }

    public function test_member_can_comment_on_a_post(): void
    {
        $author = $this->makeUser('member');
        $commenter = $this->makeUser('member');
        $post = $this->makePost($author);

        $this->actingAs($commenter)
            ->post(route('community.comments.store', $post), ['body' => 'Great point!'])
            ->assertRedirect();

        $this->assertDatabaseHas('community_comments', [
            'community_post_id' => $post->id,
            'user_id' => $commenter->id,
            'body' => 'Great point!',
        ]);
    }

    public function test_prohibited_word_blocks_a_comment(): void
    {
        ProhibitedWord::create(['word' => 'badword', 'category' => 'profanity', 'is_active' => true]);
        $author = $this->makeUser('member');
        $commenter = $this->makeUser('member');
        $post = $this->makePost($author);

        $this->actingAs($commenter)
            ->post(route('community.comments.store', $post), ['body' => 'That was a badword thing.'])
            ->assertSessionHasErrors('body');

        $this->assertDatabaseMissing('community_comments', [
            'community_post_id' => $post->id,
        ]);
    }

    public function test_author_can_delete_their_own_comment(): void
    {
        $author = $this->makeUser('member');
        $post = $this->makePost($author);
        $comment = CommunityComment::create([
            'community_post_id' => $post->id,
            'user_id' => $author->id,
            'body' => 'My comment',
            'moderation_status' => 'safe',
        ]);

        $this->actingAs($author)
            ->delete(route('community.comments.destroy', $comment))
            ->assertRedirect();

        $this->assertDatabaseMissing('community_comments', ['id' => $comment->id]);
    }

    public function test_other_member_cannot_delete_someone_elses_comment(): void
    {
        $author = $this->makeUser('member');
        $otherMember = $this->makeUser('member');
        $post = $this->makePost($author);
        $comment = CommunityComment::create([
            'community_post_id' => $post->id,
            'user_id' => $author->id,
            'body' => 'My comment',
            'moderation_status' => 'safe',
        ]);

        $this->actingAs($otherMember)
            ->delete(route('community.comments.destroy', $comment))
            ->assertStatus(403);
    }

    public function test_member_can_react_to_a_post(): void
    {
        $author = $this->makeUser('member');
        $reactor = $this->makeUser('member');
        $post = $this->makePost($author);

        $this->actingAs($reactor)
            ->post(route('community.reactions.toggle', $post))
            ->assertRedirect();

        $this->assertDatabaseHas('community_reactions', [
            'community_post_id' => $post->id,
            'user_id' => $reactor->id,
        ]);
    }

    public function test_reacting_twice_removes_the_reaction(): void
    {
        $author = $this->makeUser('member');
        $reactor = $this->makeUser('member');
        $post = $this->makePost($author);

        $this->actingAs($reactor)->post(route('community.reactions.toggle', $post));
        $this->actingAs($reactor)->post(route('community.reactions.toggle', $post));

        $this->assertDatabaseMissing('community_reactions', [
            'community_post_id' => $post->id,
            'user_id' => $reactor->id,
        ]);
    }

    public function test_guest_cannot_comment(): void
    {
        $author = $this->makeUser('member');
        $post = $this->makePost($author);

        $this->post(route('community.comments.store', $post), ['body' => 'Hi'])
            ->assertRedirect(route('login'));
    }
}