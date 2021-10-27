<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NathanHeffley\LaravelSlackBlocks\Messages\SlackAttachment;
use NathanHeffley\LaravelSlackBlocks\Messages\SlackMessage;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Support\ExceptionRenderer;
use Spatie\BackupServer\Tasks\Backup\Events\BackupFailedEvent;

class BackupFailedNotification extends Notification implements ShouldQueue
{
    use HandlesNotifications;
    use Queueable;

    public function __construct(
        private BackupFailedEvent $event
    ) {
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->error()
            ->from($this->fromEmail(), $this->fromName())
            ->subject(trans('backup-server::notifications.backup_failed_subject', $this->translationParameters()))
            ->greeting(trans('backup-server::notifications.backup_failed_subject_title', $this->translationParameters()))
            ->line(trans('backup-server::notifications.backup_failed_body', $this->translationParameters()))
            ->line(trans('backup-server::notifications.exception_title'))
            ->line(new ExceptionRenderer($this->event->exceptionMessage, $this->event->trace));
    }

    public function toSlack(): SlackMessage
    {
        return $this->slackMessage()
            ->error()
            ->from(config('backup-server.notifications.slack.username'))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title(trans('backup-server::notifications.backup_failed_subject_title', $this->translationParameters()))
                    ->content(trans('backup-server::notifications.backup_failed_body', $this->translationParameters()))
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
