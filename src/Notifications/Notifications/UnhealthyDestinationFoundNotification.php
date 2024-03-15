<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Tasks\Monitor\Events\UnhealthyDestinationFoundEvent;

class UnhealthyDestinationFoundNotification extends Notification implements ShouldQueue
{
    use HandlesNotifications;
    use Queueable;

    public function __construct(
        public UnhealthyDestinationFoundEvent $event
    ) {
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage())
            ->error()
            ->from($this->fromEmail(), $this->fromName())
            ->subject(trans('backup-server::notifications.unhealthy_destination_found_subject', $this->translationParameters()))
            ->greeting(trans('backup-server::notifications.unhealthy_destination_found_subject_title', $this->translationParameters()))
            ->line(trans('backup-server::notifications.unhealthy_destination_found_body', $this->translationParameters()))
            ->line([
                "Found problems:\n* " . collect($this->event->failureMessages)->join("\n* "),
            ]);
    }

    public function toSlack(): SlackMessage
    {
        $message = $this->slackMessage()
            ->error()
            ->from(config('backup-server.notifications.slack.username'))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title(trans('backup-server::notifications.unhealthy_destination_found_subject_title', $this->translationParameters()))
                    ->content(trans('backup-server::notifications.unhealthy_destination_found_body', $this->translationParameters()))
                    ->fallback(trans('backup-server::notifications.unhealthy_destination_found_body', $this->translationParameters()))
                    ->fields([
                        'Destination' => $this->event->destination->name,
                    ]);
            });

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
            'destination_name' => $this->event->destination->name,
        ];
    }
}
