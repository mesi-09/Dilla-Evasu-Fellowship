<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

/**
 * A short in-app notification: a sentence and a link.
 *
 * Deliberately not queued (no ShouldQueue). Queued notifications only go out
 * while a queue worker is running, and saving one row is cheap enough to do
 * straight away.
 */
class AppNotification extends Notification
{
    public function __construct(
        protected string $message,
        protected string $url,
    ) {
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * @return array<string, string>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'url' => $this->url,
        ];
    }
}