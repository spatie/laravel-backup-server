<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Tasks\Monitor\Events\UnhealthySourceFoundEvent;

class UnhealthySourceFoundNotification extends Notification implements ShouldQueue
{
    use HandlesNotifications, Queueable;

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

    public function toSlack(): SlackMessage
    {
        $message = (new SlackMessage)
            ->success()
            ->content(trans('backup::notifications.unhealthy_source_found_subject', ['destination_name' => $this->sourceName()]));

        foreach ($this->event->failureMessages as $failureMessage) {
            $message->attachment(function (SlackAttachment $attachment) use ($failureMessage) {
                $attachment->content($failureMessage);
            });
        }

        return $message;
    }

    public function sourceName(): string
    {
        return $this->event->source->name;
    }
}
