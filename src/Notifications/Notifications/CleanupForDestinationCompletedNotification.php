<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForDestinationCompletedEvent;

class CleanupForDestinationCompletedNotification extends Notification implements ShouldQueue
{
    use HandlesNotifications, Queueable;

    public CleanupForDestinationCompletedEvent $event;

    public function __construct(CleanupForDestinationCompletedEvent $event)
    {
        $this->event = $event;
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->from($this->fromEmail(), $this->fromName())
            ->subject(trans('backup::notifications.cleanup_destination_successful_subject', ['destination_name' => $this->destinationName()]))
            ->line(trans('backup::notifications.cleanup_successful_body', ['destination_name' => $this->destinationName()]));
    }

    public function toSlack(): SlackMessage
    {
        return $this->slackMessage()
            ->success()
            ->content(trans('backup::notifications.cleanup_destination_successful_subject_title'));
    }

    public function destinationName(): string
    {
        return $this->event->destination->name;
    }
}
