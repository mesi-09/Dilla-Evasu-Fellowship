<?php

namespace Tests\Unit;

use App\Models\ProhibitedWord;
use App\Services\CommunityModerationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityModerationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CommunityModerationService $moderation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->moderation = new CommunityModerationService();
    }

    public function test_clean_message_is_safe(): void
    {
        ProhibitedWord::create(['word' => 'badword', 'category' => 'profanity', 'is_active' => true]);

        $this->assertTrue($this->moderation->isSafe('Does anyone know what time Bible study starts today?'));
    }

    public function test_message_with_prohibited_word_is_not_safe(): void
    {
        ProhibitedWord::create(['word' => 'badword', 'category' => 'profanity', 'is_active' => true]);

        $this->assertFalse($this->moderation->isSafe('You are such a badword for saying that.'));
    }

    public function test_matching_is_case_insensitive(): void
    {
        ProhibitedWord::create(['word' => 'badword', 'category' => 'profanity', 'is_active' => true]);

        $this->assertFalse($this->moderation->isSafe('BADWORD is not allowed here.'));
    }

    public function test_does_not_false_positive_on_partial_word_matches(): void
    {
        ProhibitedWord::create(['word' => 'ass', 'category' => 'profanity', 'is_active' => true]);

        // "class" and "assistance" both contain "ass" but should not trigger.
        $this->assertTrue($this->moderation->isSafe('I need assistance with my class schedule.'));
    }

    public function test_inactive_prohibited_word_does_not_block_message(): void
    {
        ProhibitedWord::create(['word' => 'badword', 'category' => 'profanity', 'is_active' => false]);

        $this->assertTrue($this->moderation->isSafe('This message contains badword but the rule is inactive.'));
    }

    public function test_empty_prohibited_word_list_allows_everything(): void
    {
        $this->assertTrue($this->moderation->isSafe('Anything goes when the list is empty.'));
    }
}