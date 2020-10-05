<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Source;

use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckResult;

class MaximumStorageInMB extends SourceHealthCheck
{
    private int $configuredMaximumStorageInMB;

    public function __construct(int $configuredMaximumStorageInMB)
    {
        $this->configuredMaximumStorageInMB = $configuredMaximumStorageInMB;
    }

    public function getResult(Source $source): HealthCheckResult
    {
        $actualSizeInMB = round((int)$source->completedBackups()->sum('real_size_in_kb') / 1024, 5);

        $maximumSizeInMB = $this->maximumSizeInMB($source);

        if ($actualSizeInMB > $maximumSizeInMB) {
            return HealthCheckResult::failed("The actual source storage used ({$actualSizeInMB} MB) is greater than the allowed storage used ({$maximumSizeInMB}).");
        }

        return HealthCheckResult::ok();
    }

    protected function maximumSizeInMB(Source $source): int
    {
        $maximumSizeOnSource = $source->healthy_maximum_storage_in_mb;
        $maximumAgeOnDestination = optional($source->destination)->healthy_maximum_storage_in_mb;

        return $maximumSizeOnSource ?? $maximumAgeOnDestination ?? $this->configuredMaximumStorageInMB;
    }
}
