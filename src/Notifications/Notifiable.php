<?php

namespace Spatie\BackupServer\Notifications;

use Illuminate\Notifications\Notifiable as NotifiableTrait;

class Notifiable
{
    use NotifiableTrait;

    public function routeNotificationForMail()
    {
        return config('backup-server.notifications.mail.to');
    }

    public function routeNotificationForSlack()
    {
        return config('backup-server.notifications.slack.webhook_url');
    }

    public function getKey(): int
    {
        return 1;
    }
}
