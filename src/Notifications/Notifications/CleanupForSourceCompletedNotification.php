<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NathanHeffley\LaravelSlackBlocks\Messages\SlackAttachment;
use NathanHeffley\LaravelSlackBlocks\Messages\SlackMessage;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForSourceCompletedEvent;

class CleanupForSourceCompletedNotification extends Notification implements ShouldQueue
{
    use HandlesNotifications, Queueable;

    public CleanupForSourceCompletedEvent $event;

    public function __construct(CleanupForSourceCompletedEvent $event)
    {
        $this->event = $event;
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->success()
            ->from($this->fromEmail(), $this->fromName())
            ->subject(trans('backup-server::notifications.cleanup_source_successful_subject', $this->translationParameters()))
            ->greeting(trans('backup-server::notifications.cleanup_source_successful_subject_title', $this->translationParameters()))
            ->line(trans('backup-server::notifications.cleanup_source_successful_body', $this->translationParameters()));
    }

    public function toSlack(): SlackMessage
    {
        return $this->slackMessage()
            ->success()
            ->from(config('backup-server.notifications.slack.username'))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title(trans('backup-server::notifications.cleanup_source_successful_subject_title', $this->translationParameters()))
                    ->content(trans('backup-server::notifications.cleanup_source_successful_body', $this->translationParameters()))
                    ->fallback(trans('backup-server::notifications.cleanup_source_successful_body', $this->translationParameters()))
                    ->fields([
                        'Source' => $this->event->source->name,
                    ]);
            });
    }

    public function translationParameters(): array
    {
        return [
            'source_name' => $this->event->source->name,
        ];
    }
}
