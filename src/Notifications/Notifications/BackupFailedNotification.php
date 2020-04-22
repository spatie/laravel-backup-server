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
            ->greeting(trans('backup-server::notifications.backup_failed_subject_title', $this->translationParameters()))
            ->line(trans('backup-server::notifications.backup_failed_body', $this->translationParameters()))
            ->line([
                trans('backup-server::notifications.exception_message_title'),
                "`{$this->event->getExceptionMessage()}`",
            ])
            ->line([
                trans('backup-server::notifications.exception_trace_title'),
                "\n```{$this->event->getTrace()}```",
            ]);
    }

    public function toSlack(): SlackMessage
    {
        return $this->slackMessage()
            ->error()
            ->from(config('backup-server.notifications.slack.username'))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title(trans('backup-server::notifications.backup_failed_subject_title', $this->translationParameters()))
                    ->fallback(trans('backup-server::notifications.backup_failed_body', $this->translationParameters()))
                    ->fields([
                        'Source' => $this->event->backup->source->name,
                        'Destination' => $this->event->backup->destination->name,
                    ]);
            })
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title(trans('backup-server::notifications.exception_message_title'))
                    ->content("```{$this->event->getExceptionMessage()}```");
            })
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title(trans('backup-server::notifications.exception_trace_title'))
                    ->content("```{$this->event->getTrace()}```");
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
