<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination;

use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckResult;

class MaximumStorageInMB extends DestinationHealthCheck
{
    private int $configuredMaximumStorageInMB;

    public function __construct(int $configuredMaximumStorageInMB)
    {
        $this->configuredMaximumStorageInMB = $configuredMaximumStorageInMB;
    }

    public function getResult(Destination $destination): HealthCheckResult
    {
        $actualSizeInMB = round($destination->completedBackups()->sum('real_size_in_kb') / 1024, 5);

        $maximumSizeInMB = $this->maximumSizeInMB($destination);

        if ($actualSizeInMB > $maximumSizeInMB) {
            return HealthCheckResult::failed("The actual storage used ({$actualSizeInMB} MB) is greater than the allowed storage used ({$maximumSizeInMB}).");
        }

        return HealthCheckResult::ok();
    }

    protected function maximumSizeInMB(Destination $destination): int
    {
        return $destination->healthy_maximum_storage_in_mb ?? $this->configuredMaximumStorageInMB;
    }
}
