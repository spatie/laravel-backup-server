<?php

return [
    /*
     * This is the date format that will be used when displaying time related information on backups.
     */
    'date_format' => 'Y-m-d H:i',

    'notifications' => [

        /*
         * Backup server sends out notifications on several events. Out of the box, mails and Slack messages
         * can be sent.
         */
        'notifications' => [
            \Spatie\BackupServer\Notifications\Notifications\BackupCompletedNotification::class => ['mail'],
            \Spatie\BackupServer\Notifications\Notifications\BackupFailedNotification::class => ['mail'],

            \Spatie\BackupServer\Notifications\Notifications\CleanupForSourceCompletedNotification::class => ['mail'],
            \Spatie\BackupServer\Notifications\Notifications\CleanupForSourceFailedNotification::class => ['mail'],
            \Spatie\BackupServer\Notifications\Notifications\CleanupForDestinationCompletedNotification::class => ['mail'],
            \Spatie\BackupServer\Notifications\Notifications\CleanupForDestinationFailedNotification::class => ['mail'],

            \Spatie\BackupServer\Notifications\Notifications\HealthySourceFoundNotification::class => ['mail'],
            \Spatie\BackupServer\Notifications\Notifications\UnhealthySourceFoundNotification::class => ['mail'],
            \Spatie\BackupServer\Notifications\Notifications\HealthyDestinationFoundNotification::class => ['mail'],
            \Spatie\BackupServer\Notifications\Notifications\UnhealthyDestinationFoundNotification::class => ['mail'],
        ],

        /*
         * Here you can specify the notifiable to which the notifications should be sent. The default
         * notifiable will use the variables specified in this config file.
         */
        'notifiable' => \Spatie\BackupServer\Notifications\Notifiable::class,

        'mail' => [
            'to' => 'your@example.com',

            'from' => [
                'address' => env('MAIL_FROM_ADDRESS', 'hello@example.com'),
                'name' => env('MAIL_FROM_NAME', 'Example'),
            ],
        ],

        'slack' => [
            'webhook_url' => '',

            /*
             * If this is set to null the default channel of the web hook will be used.
             */
            'channel' => null,

            'username' => null,

            'icon' => null,

        ],
    ],

    'monitor' => [
        /*
         * These checks will be used to determine whether a source is health. The given value will be used
         * when there is no value for the check specified on either the destination or the source.
         */
        'source_health_checks' => [
            \Spatie\BackupServer\Tasks\Monitor\HealthChecks\Source\MaximumStorageInMB::class => 5000,
            \Spatie\BackupServer\Tasks\Monitor\HealthChecks\Source\MaximumAgeInDays::class => 1,
        ],

        /*
         * These checks will be used to determine whether a destination is healthy. The given value will be used
         * when there is no value for the check specified on either the destination or the source.
         */
        'destination_health_checks' => [
            \Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination\DestinationReachable::class,
            \Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination\MaximumDiskCapacityUsageInPercentage::class => 90,
            \Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination\MaximumStorageInMB::class => 0,
            \Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination\MaximumInodeUsageInPercentage::class => 90,
        ],
    ],

    /*
     * This class is responsible for deciding when sources should be backed up. An valid backup scheduler
     * is any class that implements `Spatie\BackupServer\Tasks\Backup\Support\BackupScheduler\BackupScheduler`.
     */
    'scheduler' => \Spatie\BackupServer\Tasks\Backup\Support\BackupScheduler\DefaultBackupScheduler::class,
];
