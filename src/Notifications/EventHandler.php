<?php

namespace Spatie\Backup\Notifications;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Notifications\Notification;
use Spatie\Backup\Exceptions\NotificationCouldNotBeSent;
use Spatie\BackupServer\Tasks\Backup\Events\BackupCompletedEvent;
use Spatie\BackupServer\Tasks\Backup\Events\BackupFailedEvent;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\Events\CleanupCompletedEvent;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\Events\CleanupFailedEvent;

class EventHandler
{
    protected Repository $config;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }

    public function subscribe(Dispatcher $events)
    {
        $events->listen($this->allBackupEventClasses(), function ($event) {
            $notifiable = $this->determineNotifiable();

            $notification = $this->determineNotification($event);

            $notifiable->notify($notification);
        });
    }

    protected function determineNotifiable()
    {
        $notifiableClass = $this->config->get('backup-server.notifications.notifiable');

        return app($notifiableClass);
    }

    protected function determineNotification($event): Notification
    {
        $eventName = class_basename($event);

        $notificationClass = collect($this->config->get('backup-server.notifications.notifications'))
            ->keys()
            ->first(function ($notificationClass) use ($eventName) {
                $notificationName = class_basename($notificationClass);

                return $notificationName === $eventName;
            });

        if (! $notificationClass) {
            throw NotificationCouldNotBeSent::noNotificationClassForEvent($event);
        }

        return new $notificationClass($event);
    }

    protected function allBackupEventClasses(): array
    {
        return [
            BackupCompletedEvent::class,
            BackupFailedEvent::class,
            CleanupCompletedEvent::class,
            CleanupFailedEvent::class,
        ];
    }
}
