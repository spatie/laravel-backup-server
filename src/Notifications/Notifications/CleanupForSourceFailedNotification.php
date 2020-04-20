<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForSourceFailedEvent;

class CleanupForSourceFailedNotification extends Notification implements ShouldQueue
{
    use HandlesNotifications, Queueable;

    public CleanupForSourceFailedEvent $event;

    public function __construct(CleanupForSourceFailedEvent $event)
    {
        $this->event = $event;
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->from($this->fromEmail(), $this->fromName())
            ->subject(trans('backup-server::notifications.cleanup_source_failed_subject', $this->translationParameters()))
            ->line(trans('backup-server::notifications.cleanup_failed_body', $this->translationParameters()));
    }

    public function toSlack(): SlackMessage
    {
        return $this->slackMessage()
            ->error()
            ->content(trans('backup-server::notifications.cleanup_source_failed_subject', $this->translationParameters()));
    }

    protected function translationParameters(): array
    {
        return [
            'source_name' => $this->event->source->name,
        ];
    }
}
