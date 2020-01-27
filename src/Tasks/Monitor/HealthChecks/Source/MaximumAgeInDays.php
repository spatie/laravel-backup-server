<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Source;

use Illuminate\Support\Str;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckResult;

class MaximumAgeInDays extends SourceHealthCheck
{
    private int $configuredMaximumAgeInDays;

    public function __construct(int $configuredMaximumAgeInDays)
    {
        $this->configuredMaximumAgeInDays = $configuredMaximumAgeInDays;
    }

    public function getResult(Source $source): HealthCheckResult
    {
        if ($source->created_at->diffInDays() < 1) {
            return HealthCheckResult::ok();
        }

        $latestBackup = $source->latestCompletedBackup();

        if (! $latestBackup) {
            return HealthCheckResult::failed("No backup found");
        }

        $maximumHealthAgeInDays = $this->maximumHealthyAgeInDays($source);

        if ($latestBackup->created_at->diffInDays() > $this->maximumHealthyAgeInDays($source)) {
            return HealthCheckResult::failed("Latest backup iis older then {$maximumHealthAgeInDays}" . Str::plural('day', $maximumHealthAgeInDays));
        }

        return HealthCheckResult::ok();
    }

    public function maximumHealthyAgeInDays(Source $source): int
    {
        $maximumAgeOnSource = $source->healthy_maximum_backup_age_in_days;
        $maximumAgeOnDestination = $source->destination->healthy_maximum_backup_age_in_days_per_source;

        return $maximumAgeOnSource ?? $maximumAgeOnDestination ?? $this->configuredMaximumAgeInDays;
    }
}
