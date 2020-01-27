<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Source;

use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckResponse;

class MaximumStorageInMegabytes extends SourceHealthCheck
{
    private int $configuredMaximumStorageInMegabytes;

    public function __construct(int $configuredMaximumStorageInMegabytes)
    {
        $this->configuredMaximumStorageInMegabytes = $configuredMaximumStorageInMegabytes;
    }

    public function passes(Source $source): HealthCheckResponse
    {
        $actualSizeInMegabytes = $source->completedBackups()->sum('real_size_in_kb') * 1024;

        $maximumSizeInMegabytes = $this->maximumSizeInMegabytes($source);

        if ($actualSizeInMegabytes > $maximumSizeInMegabytes) {
            HealthCheckResponse::fails("The actual storage used ({$actualSizeInMegabytes} MB) is greater than the allowed storage used ({$maximumSizeInMegabytes}).");
        }

        return HealthCheckResponse::passes();
    }

    protected function maximumSizeInMegabytes(Source $source): int
    {
        $maximumSizeOnSource = $source->healthy_maximum_storage_in_megabytes;
        $maximumAgeOnDestination = $source->destination->healthy_maximum_storage_in_megabytes;

        return $maximumSizeOnSource ?? $maximumAgeOnDestination ?? $this->configuredMaximumStorageInMegabytes;
    }
}
