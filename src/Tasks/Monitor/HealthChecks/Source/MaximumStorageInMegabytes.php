<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Source;

use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckResult;

class MaximumStorageInMegabytes extends SourceHealthCheck
{
    private int $configuredMaximumStorageInMegabytes;

    public function __construct(int $configuredMaximumStorageInMegabytes)
    {
        $this->configuredMaximumStorageInMegabytes = $configuredMaximumStorageInMegabytes;
    }

    public function getResult(Source $source): HealthCheckResult
    {
        $actualSizeInMegabytes = $source->completedBackups()->sum('real_size_in_kb') * 1024;

        $maximumSizeInMegabytes = $this->maximumSizeInMegabytes($source);

        if ($actualSizeInMegabytes > $maximumSizeInMegabytes) {
            HealthCheckResult::failed("The actual storage used ({$actualSizeInMegabytes} MB) is greater than the allowed storage used ({$maximumSizeInMegabytes}).");
        }

        return HealthCheckResult::ok();
    }

    protected function maximumSizeInMegabytes(Source $source): int
    {
        $maximumSizeOnSource = $source->healthy_maximum_storage_in_megabytes;
        $maximumAgeOnDestination = $source->destination->healthy_maximum_storage_in_megabytes;

        return $maximumSizeOnSource ?? $maximumAgeOnDestination ?? $this->configuredMaximumStorageInMegabytes;
    }
}
