<?php

namespace Spatie\BackupServer\Notifications\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\SlackAttachment;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;
use Spatie\BackupServer\Notifications\Notifications\Concerns\HandlesNotifications;
use Spatie\BackupServer\Support\Helpers\Format;
use Spatie\BackupServer\Tasks\Monitor\Events\HealthyDestinationFoundEvent;

class HealthyDestinationFoundNotification extends Notification implements ShouldQueue
{
    use HandlesNotifications;
    use Queueable;

    public function __construct(
        public HealthyDestinationFoundEvent $event
    ) {}

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->from($this->fromEmail(), $this->fromName())
            ->subject(trans('backup-server::notifications.healthy_destination_found_subject', $this->translationParameters()))
            ->greeting(trans('backup-server::notifications.healthy_destination_found_subject_title', $this->translationParameters()))
            ->line(trans('backup-server::notifications.healthy_destination_found_body', $this->translationParameters()));
    }

    public function toSlack(): SlackMessage
    {
        return $this->slackMessage()
            ->success()
            ->from(config('backup-server.notifications.slack.username'))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title(trans('backup-server::notifications.healthy_destination_found_subject_title', $this->translationParameters()))
                    ->content(trans('backup-server::notifications.healthy_destination_found_body', $this->translationParameters()))
                    ->fallback(trans('backup-server::notifications.healthy_destination_found_body', $this->translationParameters()))
                    ->fields([
                        'Destination' => $this->event->destination->name,
                        'Space used' => Format::KbToHumanReadableSize($this->event->destination->getFreeSpaceInKb()),
                        'Space used (%)' => $this->event->destination->getUsedSpaceInPercentage().'%',
                        'Inodes used (%)' => $this->event->destination->getInodeUsagePercentage().'%',
                    ]);
            });
    }

    protected function translationParameters(): array
    {
        return [
            'destination_name' => $this->event->destination->name,
        ];
    }
}
