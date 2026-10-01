<?php

namespace App\Services;

use App\Models\ProhibitedWord;

class CommunityModerationService
{
    /**
     * Check a message body against the active prohibited-word list.
     * Returns true if the content is safe to publish, false if it
     * contains a clearly prohibited word.
     */
    public function isSafe(string $content): bool
    {
        return ! $this->containsProhibitedWord($content);
    }

    /**
     * Whether the content contains any active prohibited word, matched
     * as a whole word (case-insensitive) so partial matches inside
     * unrelated words don't trigger false positives.
     */
    public function containsProhibitedWord(string $content): bool
    {
        $words = ProhibitedWord::where('is_active', true)->pluck('word');

        foreach ($words as $word) {
            $pattern = '/\b'.preg_quote($word, '/').'\b/iu';

            if (preg_match($pattern, $content) === 1) {
                return true;
            }
        }

        return false;
    }
}