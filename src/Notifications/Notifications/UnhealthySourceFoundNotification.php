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
        return (new MailMessage())
            ->from($this->fromEmail(), $this->fromName())
            ->subject(trans('backup-server::notifications.unhealthy_source_found_subject', $this->translationParameters()))
            ->line(trans('backup-server::notifications.unhealthy_source_found_body', $this->translationParameters()))
            ->line("Found problems: " . collect($this->event->failureMessages)->join(', '));
    }

    public function toSlack(): SlackMessage
    {
        $message = (new SlackMessage())
            ->success()
            ->content(trans('backup-server::notifications.unhealthy_source_found_subject', $this->translationParameters()));

        foreach ($this->event->failureMessages as $failureMessage) {
            $message->attachment(function (SlackAttachment $attachment) use ($failureMessage) {
                $attachment->content($failureMessage);
            });
        }

        return $message;
    }

    protected function translationParameters(): array
    {
        return [
            'source_name' => $this->event->source->name,
        ];
    }
}
