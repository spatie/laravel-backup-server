<?php

use Carbon\CarbonInterval;
use Spatie\BackupServer\Notifications\Notifiable;
use Spatie\BackupServer\Notifications\Notifications\BackupCompletedNotification;
use Spatie\BackupServer\Notifications\Notifications\BackupFailedNotification;
use Spatie\BackupServer\Notifications\Notifications\CleanupForDestinationCompletedNotification;
use Spatie\BackupServer\Notifications\Notifications\CleanupForDestinationFailedNotification;
use Spatie\BackupServer\Notifications\Notifications\CleanupForSourceCompletedNotification;
use Spatie\BackupServer\Notifications\Notifications\CleanupForSourceFailedNotification;
use Spatie\BackupServer\Notifications\Notifications\HealthyDestinationFoundNotification;
use Spatie\BackupServer\Notifications\Notifications\HealthySourceFoundNotification;
use Spatie\BackupServer\Notifications\Notifications\ServerSummaryNotification;
use Spatie\BackupServer\Notifications\Notifications\UnhealthyDestinationFoundNotification;
use Spatie\BackupServer\Notifications\Notifications\UnhealthySourceFoundNotification;
use Spatie\BackupServer\Tasks\Backup\Support\BackupScheduler\DefaultBackupScheduler;
use Spatie\BackupServer\Tasks\Cleanup\Strategies\DefaultCleanupStrategy;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination\DestinationReachable;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination\MaximumDiskCapacityUsageInPercentage;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination\MaximumInodeUsageInPercentage;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\Source\MaximumAgeInDays;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\Source\MaximumStorageInMB;

return [
    /*
     * This is the date format that will be used when displaying time related information on backups.
     */
    'date_format' => 'Y-m-d H:i',

    'backup' => [
        /*
         * This class is responsible for deciding when sources should be backed up. An valid backup scheduler
         * is any class that implements `Spatie\BackupServer\Tasks\Backup\Support\BackupScheduler\BackupScheduler`.
         */
        'scheduler' => DefaultBackupScheduler::class,
    ],

    'notifications' => [

        /*
         * Backup Server sends out notifications on several events. Out of the box, mails and Slack messages
         * can be sent.
         */
        'notifications' => [
            BackupCompletedNotification::class => ['mail'],
            BackupFailedNotification::class => ['mail'],

            CleanupForSourceCompletedNotification::class => ['mail'],
            CleanupForSourceFailedNotification::class => ['mail'],
            CleanupForDestinationCompletedNotification::class => ['mail'],
            CleanupForDestinationFailedNotification::class => ['mail'],

            HealthySourceFoundNotification::class => ['mail'],
            UnhealthySourceFoundNotification::class => ['mail'],
            HealthyDestinationFoundNotification::class => ['mail'],
            UnhealthyDestinationFoundNotification::class => ['mail'],

            ServerSummaryNotification::class => ['mail'],
        ],

        /*
         * Here you can specify the notifiable to which the notifications should be sent. The default
         * notifiable will use the variables specified in this config file.
         */
        'notifiable' => Notifiable::class,

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

            'username' => 'Backup Server',

            'icon' => null,

        ],
    ],

    'monitor' => [
        /*
         * These checks will be used to determine whether a source is health. The given value will be used
         * when there is no value for the check specified on either the destination or the source.
         */
        'source_health_checks' => [
            MaximumStorageInMB::class => 5000,
            MaximumAgeInDays::class => 2,
        ],

        /*
         * These checks will be used to determine whether a destination is healthy. The given value will be used
         * when there is no value for the check specified on either the destination or the source.
         */
        'destination_health_checks' => [
            DestinationReachable::class,
            MaximumDiskCapacityUsageInPercentage::class => 90,
            Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination\MaximumStorageInMB::class => 0,
            MaximumInodeUsageInPercentage::class => 90,
        ],
    ],

    'cleanup' => [
        /*
         * The strategy that will be used to cleanup old backups. The default strategy
         * will keep all backups for a certain amount of days. After that period only
         * a daily backup will be kept. After that period only weekly backups will
         * be kept and so on.
         *
         * No matter how you configure it the default strategy will never
         * delete the newest backup.
         */
        'strategy' => DefaultCleanupStrategy::class,

        'default_strategy' => [

            /*
             * The number of days for which backups must be kept.
             */
            'keep_all_backups_for_days' => 7,

            /*
             * The number of days for which daily backups must be kept.
             */
            'keep_daily_backups_for_days' => 31,

            /*
             * The number of weeks for which one weekly backup must be kept.
             */
            'keep_weekly_backups_for_weeks' => 8,

            /*
             * The number of months for which one monthly backup must be kept.
             */
            'keep_monthly_backups_for_months' => 4,

            /*
             * The number of years for which one yearly backup must be kept.
             */
            'keep_yearly_backups_for_years' => 2,

            /*
             * After cleaning up the backups remove the oldest backup until
             * this amount of megabytes has been reached.
             */
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],

    /*
     * Here you can specify on which connection the backup server jobs will be dispatched.
     * Leave empty to use the app default's env('QUEUE_CONNECTION')
     */
    'queue_connection' => '',

    'jobs' => [
        'perform_backup_job' => [
            'queue' => 'backup-server-backup',
            'timeout' => CarbonInterval::hour(1)->totalSeconds,
        ],
        'delete_backup_job' => [
            'queue' => 'backup-server',
            'timeout' => CarbonInterval::minutes(1)->totalSeconds,
        ],
        'delete_destination_job' => [
            'queue' => 'backup-server',
            'timeout' => CarbonInterval::hour(1)->totalSeconds,
        ],
        'delete_source_job' => [
            'queue' => 'backup-server',
            'timeout' => CarbonInterval::hour(1)->totalSeconds,
        ],
        'perform_cleanup_for_source_job' => [
            'queue' => 'backup-server-cleanup',
            'timeout' => CarbonInterval::hour(1)->totalSeconds,
        ],
        'perform_cleanup_for_destination_job' => [
            'queue' => 'backup-server-cleanup',
            'timeout' => CarbonInterval::hour(1)->totalSeconds,
        ],
    ],

    /**
     * It can take a long time to calculate the size of very large backups. If your
     * backups sometimes timeout when calculating their size you can increase this value.
     */
    'backup_size_calculation_timeout_in_seconds' => 60,

    /**
     * Calculating the size of multiple backups at once can be a very slow
     * process particularly on cloud volumes, so we allow plenty of time.
     */
    'backup_collection_size_calculation_timeout_in_seconds' => 60 * 15,
];
