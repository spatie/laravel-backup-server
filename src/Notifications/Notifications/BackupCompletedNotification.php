<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Tasks\Backup\Events\BackupCompletedEvent;

class BackupCompletedNotification
{
    use HandlesNotifications;

    public BackupCompletedEvent $event;

    public function __construct(BackupCompletedEvent $event)
    {
        $this->event = $event;
    }

    public function toMail(): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->from($this->fromEmail(), $this->fromName())
            ->subject(trans('backup::notifications.backup_completed_subject', $this->sourceName()))
            ->line(trans('backup::notifications.backup_completed_body', $this->sourceName()));

        return $mailMessage;
    }

    public function toSlack(): SlackMessage
    {
        return (new SlackMessage)
            ->success()
            ->from(config('backup-server.notifications.slack.username'), config('backup-server.notifications.slack.icon'))
            ->to(config('backup-server.notifications.slack.channel'))
            ->content(trans('backup::notifications.backup_successful_subject_title'))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->fields([
                    'source' => $this->sourceName(),
                ]);
            });
    }

    public function sourceName(): string
    {
        return $this->event->backup->source->name;
    }
}
