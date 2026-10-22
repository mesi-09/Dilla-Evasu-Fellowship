<?php

namespace App\Services;

use App\Models\Announcement;
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
 * prohibited word, a Bible message or announcement title), and no method
 * accepts free text.
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

    public function announcementCreated(User $actor, Announcement $announcement): void
    {
        $this->record($actor, 'announcement.created', "Created announcement \"{$announcement->title}\" ({$this->announcementState($announcement)}).");
    }

    public function announcementUpdated(User $actor, Announcement $announcement): void
    {
        $this->record($actor, 'announcement.updated', "Updated announcement \"{$announcement->title}\" ({$this->announcementState($announcement)}).");
    }

    public function announcementDeleted(User $actor, Announcement $announcement): void
    {
        $this->record($actor, 'announcement.deleted', "Deleted announcement \"{$announcement->title}\".");
    }

    /**
     * Logged by id only: the applicant's name, phone and email stay out.
     */
    public function coworkerApplicationReviewed(User $actor, CoworkerApplication $application): void
    {
        $status = ucfirst(str_replace('_', ' ', $application->status));

        $this->record($actor, 'coworker_application.reviewed', "Set co-worker application #{$application->id} to {$status}.");
    }

    public function userRoleChanged(User $actor, User $target, string $oldRole): void
    {
        $this->record($actor, 'user.role_changed', sprintf(
            'Changed the role of %s (#%d) from %s to %s.',
            $target->name,
            $target->id,
            $this->roleLabel($oldRole),
            $this->roleLabel($target->role)
        ));
    }

    public function userActiveChanged(User $actor, User $target): void
    {
        $activated = (bool) $target->is_active;

        $this->record(
            $actor,
            $activated ? 'user.activated' : 'user.deactivated',
            ($activated ? 'Activated' : 'Deactivated')." the account of {$target->name} (#{$target->id})."
        );
    }

    protected function roleLabel(string $role): string
    {
        return ucfirst(str_replace('_', ' ', $role));
    }

    protected function bibleMessageState(BibleMessage $message): string
    {
        $state = ucfirst($message->status);

        if ($message->status === 'scheduled' && $message->publish_date) {
            $state .= ' for '.$message->publish_date->format('M j, Y');
        }

        return $state;
    }

    protected function announcementState(Announcement $announcement): string
    {
        $state = ucfirst($announcement->status);

        return $announcement->is_important ? "{$state}, important" : $state;
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