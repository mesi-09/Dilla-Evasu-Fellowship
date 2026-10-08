<?php

namespace Tests\Feature;

use App\Models\ProhibitedWord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProhibitedWordManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function makeUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'is_active' => true,
        ]);
    }

    public function test_main_admin_can_view_the_prohibited_words_list(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->get(route('admin.prohibited-words.index'))
            ->assertStatus(200);
    }

    public function test_main_admin_can_add_a_prohibited_word(): void
    {
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->post(route('admin.prohibited-words.store'), [
                'word' => 'badword',
                'category' => 'profanity',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('prohibited_words', [
            'word' => 'badword',
            'category' => 'profanity',
            'is_active' => true,
        ]);
    }

    public function test_cannot_add_a_duplicate_word(): void
    {
        ProhibitedWord::create(['word' => 'badword', 'category' => 'profanity', 'is_active' => true]);
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->post(route('admin.prohibited-words.store'), [
                'word' => 'badword',
                'category' => 'profanity',
            ])
            ->assertSessionHasErrors('word');
    }

    public function test_main_admin_can_toggle_a_word_inactive(): void
    {
        $word = ProhibitedWord::create(['word' => 'badword', 'category' => 'profanity', 'is_active' => true]);
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->patch(route('admin.prohibited-words.toggle', $word))
            ->assertRedirect();

        $this->assertDatabaseHas('prohibited_words', [
            'id' => $word->id,
            'is_active' => false,
        ]);
    }

    public function test_main_admin_can_delete_a_word(): void
    {
        $word = ProhibitedWord::create(['word' => 'badword', 'category' => 'profanity', 'is_active' => true]);
        $admin = $this->makeUser('main_admin');

        $this->actingAs($admin)
            ->delete(route('admin.prohibited-words.destroy', $word))
            ->assertRedirect();

        $this->assertDatabaseMissing('prohibited_words', ['id' => $word->id]);
    }

    public function test_member_cannot_view_prohibited_words_list(): void
    {
        $member = $this->makeUser('member');

        $this->actingAs($member)
            ->get(route('admin.prohibited-words.index'))
            ->assertStatus(403);
    }

    public function test_member_cannot_add_a_prohibited_word(): void
    {
        $member = $this->makeUser('member');

        $this->actingAs($member)
            ->post(route('admin.prohibited-words.store'), [
                'word' => 'badword',
                'category' => 'profanity',
            ])
            ->assertStatus(403);
    }

    public function test_guest_cannot_view_prohibited_words_list(): void
    {
        $this->get(route('admin.prohibited-words.index'))
            ->assertRedirect(route('login'));
    }
}