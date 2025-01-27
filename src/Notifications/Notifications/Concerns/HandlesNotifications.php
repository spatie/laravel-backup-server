<?php

namespace Spatie\BackupServer\Notifications\Notifications\Concerns;

use Illuminate\Notifications\Messages\SlackMessage;

trait HandlesNotifications
{
    public function via(): array
    {
        $notificationChannels = config('backup-server.notifications.notifications.'.static::class);

        return array_filter($notificationChannels);
    }

    public function fromEmail(): string
    {
        return config('backup-server.notifications.mail.from.address', config('mail.from.address'));
    }

    public function fromName(): string
    {
        return config('backup-server.notifications.mail.from.name', config('mail.from.name'));
    }

    public function slackMessage(): SlackMessage
    {
        return (new SlackMessage)
            ->from(config('backup-server.notifications.slack.username'), config('backup-server.notifications.slack.icon'))
            ->to(config('backup-server.notifications.slack.channel'));
    }
}
