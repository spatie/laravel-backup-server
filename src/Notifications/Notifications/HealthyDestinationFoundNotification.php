<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Tasks\Monitor\Events\HealthyDestinationFoundEvent;

class HealthyDestinationFoundNotification extends Notification implements ShouldQueue
{
    use HandlesNotifications, Queueable;

    public HealthyDestinationFoundEvent $event;

    public function __construct(HealthyDestinationFoundEvent $event)
    {
        $this->event = $event;
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->from($this->fromEmail(), $this->fromName())
            ->subject(trans('backup::notifications.healthy_destination_found_subject', ['destination_name' => $this->destinationName()]))
            ->line(trans('backup::notifications.healthy_destination_found_body', ['destination_name' => $this->destinationName()]));
    }

    public function toSlack(): SlackMessage
    {
        return (new SlackMessage)
            ->success()
            ->content(trans('backup::notifications.healthy_destination_found_subject', ['destination_name' => $this->destinationName()]));
    }

    public function destinationName(): string
    {
        return $this->event->destination->name;
    }
}
