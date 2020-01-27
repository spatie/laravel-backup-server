<?php

use Faker\Generator as Faker;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Cleanup\Strategies\DefaultCleanupStrategy;

/** @var \Illuminate\Database\Eloquent\Factory $factory */
$factory->define(Source::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'host' => $faker->name,
        'ssh_user' => $faker->userName,
        'includes' => ['/home/forge/dummy'],
        'excludes' => [],
        'destination_id' => factory(Destination::class),
        'cleanup_strategy_class' => DefaultCleanupStrategy::class,
        'keep_all_backups_for_days' => 7,
        'keep_daily_backups_for_days' => 16,
        'keep_weekly_backups_for_weeks' => 8,
        'keep_monthly_backups_for_months' => 4,
        'keep_yearly_backups_for_years' => 2,
        'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
    ];
});
