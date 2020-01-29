<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Tasks\Backup\Events\BackupCompletedEvent;

class BackupCompletedNotification extends Notification implements ShouldQueue
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
            ->subject(trans('backup::notifications.backup_completed_subject', ['source_name' => $this->sourceName()]))
            ->line(trans('backup::notifications.backup_completed_body', ['source_name' => $this->sourceName()]));

        return $mailMessage;
    }

    public function toSlack(): SlackMessage
    {
        return $this->slackMessage()
            ->success()
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
