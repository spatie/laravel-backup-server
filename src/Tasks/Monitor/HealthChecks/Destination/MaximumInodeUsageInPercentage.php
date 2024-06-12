<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination;

use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckResult;

class MaximumInodeUsageInPercentage extends DestinationHealthCheck
{
    public function __construct(private int $maximumPercentage)
    {
    }

    public function getResult(Destination $destination): HealthCheckResult
    {
        $currentInodeUsagePercentage = $destination->getInodeUsagePercentage();

        if ($destination->getInodeUsagePercentage() > $this->maximumPercentage) {
            HealthCheckResult::failed("The current inode usage percentage ({$currentInodeUsagePercentage}) is higher than the allowed percentage {$this->maximumPercentage}");
        }

        return HealthCheckResult::ok();
    }
}
