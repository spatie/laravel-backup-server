<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Tasks\Backup\Events\BackupCompletedEvent;

class BackupCompletedNotification extends Notification implements ShouldQueue
{
    use HandlesNotifications, Queueable;

    public BackupCompletedEvent $event;

    public function __construct(BackupCompletedEvent $event)
    {
        $this->event = $event;
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->success()
            ->from($this->fromEmail(), $this->fromName())
            ->subject(trans('backup-server::notifications.backup_completed_subject', $this->translationParameters()))
            ->greeting(trans('backup-server::notifications.backup_completed_subject_title', $this->translationParameters()))
            ->line(trans('backup-server::notifications.backup_completed_body', $this->translationParameters()));
    }

    public function toSlack(): SlackMessage
    {
        return $this->slackMessage()
            ->success()
            ->content(trans('backup-server::notifications.backup_completed_subject', $this->translationParameters()))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment->fields([
                    'source' => $this->event->backup->source->name,
                ]);
            });
    }

    protected function translationParameters(): array
    {
        return [
            'source_name' => $this->event->backup->source->name,
            'destination_name' => $this->event->backup->destination->name,
        ];
    }
}
