<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination;

use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckResult;

class MaximumStorageInMB extends DestinationHealthCheck
{

    public function __construct(
        private int $configuredMaximumStorageInMB,
    ) {}

    public function getResult(Destination $destination): HealthCheckResult
    {
        $maximumSizeInMB = $this->maximumSizeInMB($destination);

        if ($maximumSizeInMB === 0) {
            return HealthCheckResult::ok();
        }

        $actualSizeInMB = round($destination->completedBackups()->sum('real_size_in_kb') / 1024, 5);

        if ($actualSizeInMB > $maximumSizeInMB) {
            return HealthCheckResult::failed("The actual destination storage used ({$actualSizeInMB} MB) is greater than the allowed storage used ({$maximumSizeInMB}).");
        }

        return HealthCheckResult::ok();
    }

    protected function maximumSizeInMB(Destination $destination): int
    {
        return $destination->healthy_maximum_storage_in_mb ?? $this->configuredMaximumStorageInMB;
    }
}
