<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Support\Helpers\Format;
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
        return (new MailMessage())
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
            ->from(config('backup-server.notifications.slack.username'))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title(trans('backup-server::notifications.backup_completed_subject_title', $this->translationParameters()))
                    ->content(trans('backup-server::notifications.backup_completed_body', $this->translationParameters()))
                    ->fallback(trans('backup-server::notifications.backup_completed_body', $this->translationParameters()))
                    ->fields([
                        'Source' => $this->event->backup->source->name,
                        'Destination' => $this->event->backup->destination->name,
                        'Duration' => $this->event->backup->rsync_time_in_seconds . ' seconds',
                        'Average speed' => $this->event->backup->rsync_average_transfer_speed_in_MB_per_second,
                        'Size' => Format::KbTohumanReadableSize($this->event->backup->size_in_kb),
                    ])
                    ->timestamp($this->event->backup->completed_at);
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
