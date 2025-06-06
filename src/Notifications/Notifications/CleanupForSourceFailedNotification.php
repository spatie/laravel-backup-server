<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Support\ExceptionRenderer;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForSourceFailedEvent;

class CleanupForSourceFailedNotification extends Notification implements ShouldQueue
{
    use HandlesNotifications;
    use Queueable;

    public function __construct(
        public CleanupForSourceFailedEvent $event
    ) {}

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->from($this->fromEmail(), $this->fromName())
            ->subject(trans('backup-server::notifications.cleanup_source_failed_subject', $this->translationParameters()))
            ->greeting(trans('backup-server::notifications.cleanup_source_failed_subject_title', $this->translationParameters()))
            ->line(trans('backup-server::notifications.cleanup_source_failed_body', $this->translationParameters()))
            ->line(trans('backup-server::notifications.exception_title'))
            ->line(new ExceptionRenderer($this->event->exceptionMessage));
    }

    public function toSlack(): SlackMessage
    {
        return $this->slackMessage()
            ->error()
            ->from(config('backup-server.notifications.slack.username'))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title(trans('backup-server::notifications.cleanup_source_failed_subject_title', $this->translationParameters()))
                    ->content(trans('backup-server::notifications.cleanup_source_failed_body', $this->translationParameters()))
                    ->fallback(trans('backup-server::notifications.cleanup_source_failed_body', $this->translationParameters()))
                    ->fields([
                        'Destination' => $this->event->source->name,
                    ]);
            })
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title(trans('backup-server::notifications.exception_message_title'))
                    ->content("```{$this->event->exceptionMessage}\n```");
            });
    }

    protected function translationParameters(): array
    {
        return [
            'source_name' => $this->event->source->name,
        ];
    }
}
