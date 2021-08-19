<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Source;

use Illuminate\Support\Str;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckResult;

class MaximumAgeInDays extends SourceHealthCheck
{
    public function __construct(
        private int $configuredMaximumAgeInDays,
    ) {
    }

    public function getResult(Source $source): HealthCheckResult
    {
        if ($source->created_at) {
            if ($source->created_at->diffInDays() < $this->maximumHealthyAgeInDays($source)) {
                return HealthCheckResult::ok();
            }
        }

        $youngestCompletedBackup = $source->youngestCompletedBackup();

        if (! $youngestCompletedBackup) {
            return HealthCheckResult::failed("No backup found");
        }

        $maximumHealthAgeInDays = $this->maximumHealthyAgeInDays($source);

        if ($youngestCompletedBackup->created_at->diffInDays() >= $this->maximumHealthyAgeInDays($source)) {
            return HealthCheckResult::failed("Latest backup is older then {$maximumHealthAgeInDays}" . Str::plural('day', $maximumHealthAgeInDays));
        }

        return HealthCheckResult::ok();
    }

    public function maximumHealthyAgeInDays(Source $source): int
    {
        $maximumAgeOnSource = $source->healthy_maximum_backup_age_in_days;
        $maximumAgeOnDestination = optional($source->destination)->healthy_maximum_backup_age_in_days_per_source;

        return $maximumAgeOnSource ?? $maximumAgeOnDestination ?? $this->configuredMaximumAgeInDays;
    }
}
