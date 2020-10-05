<?php

namespace Spatie\BackupServer\Tests\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Cleanup\Strategies\DefaultCleanupStrategy;

class SourceFactory extends Factory
{
    protected $model = Source::class;


    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'host' => $this->faker->name,
            'ssh_user' => $this->faker->userName,
            'includes' => ['/home/forge/dummy'],
            'excludes' => [],
            'destination_id' => Destination::factory(),
            'cleanup_strategy_class' => DefaultCleanupStrategy::class,
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 16,
            'keep_weekly_backups_for_weeks' => 8,
            'keep_monthly_backups_for_months' => 4,
            'keep_yearly_backups_for_years' => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ];
    }
}
