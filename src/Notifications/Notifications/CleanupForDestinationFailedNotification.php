<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForDestinationFailedEvent;

class CleanupForDestinationFailedNotification extends Notification implements ShouldQueue
{
    use HandlesNotifications, Queueable;

    public CleanupForDestinationFailedEvent $event;

    public function __construct(CleanupForDestinationFailedEvent $event)
    {
        $this->event = $event;
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->from($this->fromEmail(), $this->fromName())
            ->subject(trans('backup-server::notifications.cleanup_destination_failed_subject', $this->translationParameters()))
            ->line(trans('backup-server::notifications.cleanup_destination_failed_body', $this->translationParameters()));
    }

    public function toSlack(): SlackMessage
    {
        return $this->slackMessage()
            ->error()
            ->content(trans('backup-server::notifications.cleanup_destination_failed_subject', $this->translationParameters()));
    }

    public function translationParameters(): array
    {
        return [
            'destination_name' => $this->event->destination->name,
        ];
    }
}
