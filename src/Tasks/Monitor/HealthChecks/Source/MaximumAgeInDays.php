<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Source;

use Illuminate\Support\Str;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckResponse;

class MaximumAgeInDays extends SourceHealthCheck
{
    private int $configuredMaximumAgeInDays;

    public function __construct(int $configuredMaximumAgeInDays)
    {
        $this->configuredMaximumAgeInDays = $configuredMaximumAgeInDays;
    }

    public function passes(Source $source): HealthCheckResponse
    {
        if ($source->created_at->ageInDays() < 1) {
            return HealthCheckResponse::passes();
        }

        $latestBackup = $source->latestCompletedBackup();

        if (! $latestBackup) {
            return HealthCheckResponse::fails("No backup found");
        }

        $maximumHealthAgeInDays = $this->maximumHealthyAgeInDays($source);

        if ($latestBackup->created_at->ageInDays() > $this->maximumHealthyAgeInDays($source)) {
            return HealthCheckResponse::fails("Latest backup iis older then {$maximumHealthAgeInDays}" . Str::plural('day', $maximumHealthAgeInDays));
        }

        return HealthCheckResponse::passes();
    }

    public function maximumHealthyAgeInDays(Source $source): int
    {
        $maximumAgeOnSource = $source->healthy_maximum_backup_age_in_days;
        $maximumAgeOnDestination = $source->destination->healthy_maximum_backup_age_in_days_per_source;

        return $maximumAgeOnSource ?? $maximumAgeOnDestination ?? $this->configuredMaximumAgeInDays;
    }
}
