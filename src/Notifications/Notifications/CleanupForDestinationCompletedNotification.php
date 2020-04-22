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
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForDestinationCompletedEvent;

class CleanupForDestinationCompletedNotification extends Notification implements ShouldQueue
{
    use HandlesNotifications, Queueable;

    public CleanupForDestinationCompletedEvent $event;

    public function __construct(CleanupForDestinationCompletedEvent $event)
    {
        $this->event = $event;
    }

    public function toMail(): MailMessage
    {
        return (new MailMessage)
            ->success()
            ->from($this->fromEmail(), $this->fromName())
            ->subject(trans('backup-server::notifications.cleanup_destination_successful_subject', $this->translationParameters()))
            ->greeting(trans('backup-server::notifications.cleanup_destination_successful_subject_title', $this->translationParameters()))
            ->line(trans('backup-server::notifications.cleanup_destination_successful_body', $this->translationParameters()));
    }

    public function toSlack(): SlackMessage
    {
        return $this->slackMessage()
            ->success()
            ->from(config('backup-server.notifications.slack.username'))
            ->attachment(function (SlackAttachment $attachment) {
                $attachment
                    ->title(trans('backup-server::notifications.cleanup_destination_successful_subject_title', $this->translationParameters()))
                    ->fallback(trans('backup-server::notifications.cleanup_destination_successful_body', $this->translationParameters()))
                    ->fields([
                        'Destination' => $this->event->destination->name,
                        'Space used' => Format::KbTohumanReadableSize($this->event->destination->getFreeSpaceInKb()),
                        '% space used' => $this->event->destination->getUsedSpaceInPercentage(),
                        '% inodes used' => $this->event->destination->getInodeUsagePercentage(),
                    ]);
            });
    }

    public function translationParameters(): array
    {
        return [
            'destination_name' => $this->event->destination->name,
        ];
    }
}
