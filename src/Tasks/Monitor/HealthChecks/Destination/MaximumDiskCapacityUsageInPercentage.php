<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination;

use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckResult;

class MaximumDiskCapacityUsageInPercentage extends DestinationHealthCheck
{
    public function __construct(private int $maximumDiskCapacityUsageInPercentage) {}

    public function getResult(Destination $destination): HealthCheckResult
    {
        $actualDiskSpaceUsage = $destination->getUsedSpaceInPercentage();

        if ($actualDiskSpaceUsage > $this->maximumDiskCapacityUsageInPercentage) {
            return HealthCheckResult::failed("The used disk space capacity ($actualDiskSpaceUsage %) is greater than the maximum allowed ($this->maximumDiskCapacityUsageInPercentage %)");
        }

        return HealthCheckResult::ok();
    }
}
