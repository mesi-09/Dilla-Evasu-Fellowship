<?php

namespace App\Services;

use App\Models\CounselingAppointment;
use App\Models\CounselingRequest;
use App\Models\LoveSharingRequest;
use App\Models\User;
use App\Notifications\AppNotification;
use Illuminate\Database\Eloquent\Model;

/**
 * Creates in-app notifications.
 *
 * Privacy rule: a notification says THAT something happened, never WHAT a
 * private request or message contains. Every message below is a fixed
 * sentence plus, at most, a status label, and no method accepts free text.
 * Recipients are decided here as well: the student who owns the request, or
 * the leaders of the matching team. The Main Admin and the other team's
 * leaders are never recipients.
 */
class NotificationService
{
    public function requestSubmitted(LoveSharingRequest|CounselingRequest $request): void
    {
        $team = $this->team($request);
        $url = $this->path($team['show'], $request);

        $this->notify($request->student, "Your {$team['label']} request was submitted.", $url);
        $this->notifyLeaders($team['role'], "A new {$team['label']} request was received.", $url);
    }

    public function statusChanged(LoveSharingRequest|CounselingRequest $request): void
    {
        $team = $this->team($request);
        $status = ucfirst(str_replace('_', ' ', $request->status));

        $this->notify(
            $request->student,
            "Your {$team['label']} request is now: {$status}.",
            $this->path($team['show'], $request)
        );
    }

    public function newMessage(LoveSharingRequest|CounselingRequest $request, User $sender): void
    {
        $team = $this->team($request);
        $url = $this->path($team['messages'], $request);
        $message = "You have a new {$team['label']} message.";

        if ($sender->id === $request->student_id) {
            $this->notifyLeaders($team['role'], $message, $url);
        } else {
            $this->notify($request->student, $message, $url);
        }
    }

    public function appointmentScheduled(CounselingAppointment $appointment): void
    {
        $this->notify(
            $appointment->student,
            'A counseling appointment has been scheduled for you.',
            $this->path('counseling-appointments.show', $appointment)
        );
    }

    /**
     * @return array{label: string, role: string, show: string, messages: string}
     */
    protected function team(Model $request): array
    {
        return $request instanceof LoveSharingRequest
            ? ['label' => 'Love Sharing', 'role' => 'love_sharing_leader', 'show' => 'love-sharing.show', 'messages' => 'love-sharing.messages.index']
            : ['label' => 'Counseling', 'role' => 'counseling_leader', 'show' => 'counseling.show', 'messages' => 'counseling.messages.index'];
    }

    /**
     * A relative path such as /love-sharing/4, not a full URL. A stored full
     * URL would break whenever the site's domain changes, which has already
     * happened once with the Codespace address.
     */
    protected function path(string $route, Model $model): string
    {
        return route($route, $model, false);
    }

    protected function notify(?User $user, string $message, string $url): void
    {
        $user?->notify(new AppNotification($message, $url));
    }

    protected function notifyLeaders(string $role, string $message, string $url): void
    {
        User::where('role', $role)
            ->where('is_active', true)
            ->each(fn (User $leader) => $this->notify($leader, $message, $url));
    }
}