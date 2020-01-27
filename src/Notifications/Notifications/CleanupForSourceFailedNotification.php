<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForSourceFailedEvent;

class CleanupForSourceFailedNotification extends Notification
{
    use HandlesNotifications;

    public CleanupForSourceFailedEvent $event;

    public function __construct(CleanupForSourceFailedEvent $event)
    {
        $this->event = $event;
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->from($this->fromEmail(), $this->fromName())
            ->subject(trans('backup::notifications.cleanup_source_failed_subject', ['source_name' => $this->sourceName()]))
            ->line(trans('backup::notifications.cleanup_failed_body', ['source_name' => $this->sourceName()]));
    }

    public function toSlack(): SlackMessage
    {
        return (new SlackMessage)
            ->success()
            ->from(config('backup.notifications.slack.username'), config('backup.notifications.slack.icon'))
            ->to(config('backup.notifications.slack.channel'))
            ->content(trans('backup::notifications.cleanup_source_failed_subject_title'));
    }

    public function sourceName(): string
    {
        return $this->event->backup->source->name;
    }
}
