<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination;

use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckResponse;

class MaximumStorageInMegabytes extends DestinationHealthCheck
{
    private int $configuredMaximumStorageInMegabytes;

    public function __construct(int $configuredMaximumStorageInMegabytes)
    {
        $this->configuredMaximumStorageInMegabytes = $configuredMaximumStorageInMegabytes;
    }

    public function passes(Destination $destination): HealthCheckResponse
    {
        $actualSizeInMegabytes = $destination->completedBackups()->sum('real_size_in_kb') * 1024;

        $maximumSizeInMegabytes = $this->maximumSizeInMegabytes($destination);

        if ($actualSizeInMegabytes > $maximumSizeInMegabytes) {
            HealthCheckResponse::fails("The actual storage used ({$actualSizeInMegabytes} MB) is greater than the allowed storage used ({$maximumSizeInMegabytes}).");
        }

        return HealthCheckResponse::passes();
    }

    protected function maximumSizeInMegabytes(Destination $destination): int
    {
        return $destination->healthy_maximum_storage_in_megabytes ?? $this->configuredMaximumStorageInMegabytes;
    }
}
