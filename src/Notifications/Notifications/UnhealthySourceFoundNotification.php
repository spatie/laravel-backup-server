<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Tasks\Monitor\Events\UnhealthySourceFoundEvent;

class UnhealthySourceFoundNotification extends Notification
{
    use HandlesNotifications;

    public UnhealthySourceFoundEvent $event;

    public function __construct(UnhealthySourceFoundEvent $event)
    {
        $this->event = $event;
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->from($this->fromEmail(), $this->fromName())
            ->subject(trans('backup::notifications.unhealthy_source_found_subject', ['source_name' => $this->sourceName()]))
            ->line(trans('backup::notifications.unhealthy_source_found_body', ['source_name' => $this->sourceName()]))
            ->line("Found problems: " . collect($this->event->failureMessages)->join(', '));
    }

    public function sourceName(): string
    {
        return $this->event->source->name;
    }
}
