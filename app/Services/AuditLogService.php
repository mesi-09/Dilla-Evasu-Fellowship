<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\BibleMessage;
use App\Models\CoworkerApplication;
use App\Models\ProhibitedWord;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Records administrative actions.
 *
 * Privacy rule: an entry says WHAT an admin did, never the content of a
 * private request or an applicant's personal details. Each sentence is built
 * here from fixed text plus identifiers the admin themselves manage (a
 * prohibited word, a Bible message title), and no method accepts free text.
 */
class AuditLogService
{
    public function prohibitedWordAdded(User $actor, ProhibitedWord $word): void
    {
        $this->record($actor, 'prohibited_word.added', "Added prohibited word \"{$word->word}\" ({$word->category}).");
    }

    public function prohibitedWordToggled(User $actor, ProhibitedWord $word): void
    {
        $state = $word->is_active ? 'Activated' : 'Deactivated';

        $this->record($actor, 'prohibited_word.toggled', "{$state} prohibited word \"{$word->word}\".");
    }

    public function prohibitedWordRemoved(User $actor, ProhibitedWord $word): void
    {
        $this->record($actor, 'prohibited_word.removed', "Removed prohibited word \"{$word->word}\".");
    }

    public function bibleMessageCreated(User $actor, BibleMessage $message): void
    {
        $this->record($actor, 'bible_message.created', "Created Bible message \"{$message->title}\" ({$this->bibleMessageState($message)}).");
    }

    public function bibleMessageUpdated(User $actor, BibleMessage $message): void
    {
        $this->record($actor, 'bible_message.updated', "Updated Bible message \"{$message->title}\" ({$this->bibleMessageState($message)}).");
    }

    public function bibleMessageDeleted(User $actor, BibleMessage $message): void
    {
        $this->record($actor, 'bible_message.deleted', "Deleted Bible message \"{$message->title}\".");
    }

    /**
     * Logged by id only: the applicant's name, phone and email stay out.
     */
    public function coworkerApplicationReviewed(User $actor, CoworkerApplication $application): void
    {
        $status = ucfirst(str_replace('_', ' ', $application->status));

        $this->record($actor, 'coworker_application.reviewed', "Set co-worker application #{$application->id} to {$status}.");
    }

    protected function bibleMessageState(BibleMessage $message): string
    {
        $state = ucfirst($message->status);

        if ($message->status === 'scheduled' && $message->publish_date) {
            $state .= ' for '.$message->publish_date->format('M j, Y');
        }

        return $state;
    }

    protected function record(User $actor, string $action, string $description): void
    {
        AuditLog::create([
            'user_id' => $actor->id,
            'actor_name' => $actor->name,
            'action' => $action,
            'description' => Str::limit($description, 500, ''),
        ]);
    }
}

