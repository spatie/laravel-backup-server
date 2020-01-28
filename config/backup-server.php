<?php

return [
    'date_format' => 'Y-m-d H:i',

    'redirect_unauthorized_users_to_route' => 'home',

    'notifications' => [

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
             * If this is set to null the default channel of the webhook will be used.
             */
            'channel' => null,

            'username' => null,

            'icon' => null,

        ],
    ],

    'monitor' => [
        'source_health_checks' => [
            \Spatie\BackupServer\Tasks\Monitor\HealthChecks\Source\MaximumStorageInMegabytes::class => 5000,
            \Spatie\BackupServer\Tasks\Monitor\HealthChecks\Source\MaximumAgeInDays::class => 1,
        ],

        'destination_health_checks' => [
            \Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination\DestinationReachable::class,
            \Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination\MaximumStorageInMegabytes::class => 0,
            \Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination\MaximumInodeUsageInPercentage::class => 90
        ]
    ],
];
