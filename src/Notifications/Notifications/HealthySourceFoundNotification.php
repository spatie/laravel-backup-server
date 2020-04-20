<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Tasks\Monitor\Events\HealthySourceFoundEvent;

class HealthySourceFoundNotification extends Notification implements ShouldQueue
{
    use HandlesNotifications, Queueable;

    public HealthySourceFoundEvent $event;

    public function __construct(HealthySourceFoundEvent $event)
    {
        $this->event = $event;
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->from($this->fromEmail(), $this->fromName())
            ->subject(trans('backup-server::notifications.healthy_source_found_subject', $this->translationParameters()))
            ->line(trans('backup-server::notifications.healthy_source_found_body', $this->translationParameters()));
    }

    public function toSlack(): SlackMessage
    {
        return (new SlackMessage())
            ->success()
            ->content(trans('backup-server::notifications.healthy_source_found_subject', $this->translationParameters()));
    }

    public function translationParameters(): array
    {
        return [
            'source_name' => $this->event->source->name,
        ];
    }
}
