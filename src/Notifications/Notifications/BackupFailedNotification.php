<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Tasks\Backup\Events\BackupFailedEvent;

class BackupFailedNotification extends Notification implements ShouldQueue
{
    use HandlesNotifications, Queueable;

    private BackupFailedEvent $event;

    public function __construct(BackupFailedEvent $event)
    {
        $this->event = $event;
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->error()
            ->from($this->fromEmail(), $this->fromName())
            ->subject(trans('backup-server::notifications.backup_failed_subject', $this->translationParameters()))
            ->line(trans('backup-server::notifications.backup_failed_body', $this->translationParameters()))
            ->line(trans('backup-server::notifications.exception_message', $this->translationParameters()))
            ->line(trans('backup-server::notifications.exception_trace', $this->translationParameters()));
    }

    public function toSlack(): SlackMessage
    {
        return $this->slackMessage()
            ->error()
            ->content(trans('backup-server::notifications.backup_failed_subject', $this->translationParameters()))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title(trans('backup-server::notifications.exception_message_title'))
                    ->fields([
                        'Source' => $this->event->backup->source->name,
                        'Destination' => $this->event->backup->destination->name,
                    ]);
            })
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title(trans('backup-server::notifications.exception_message_title'))
                    ->content($this->event->getExceptionMessage());
            })
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title(trans('backup-server::notifications.exception_trace_title'))
                    ->content($this->event->getTrace());
            });
    }

    protected function translationParameters(): array
    {
        return [
            'source_name' => $this->event->backup->source->name,
            'destination_name' => $this->event->backup->destination->name,
            'message' => $this->event->getExceptionMessage(),
            'trace' => $this->event->getTrace(),
        ];
    }
}
