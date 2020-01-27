<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination;

use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckResult;

class MaximumInodeUsageInPercentage extends DestinationHealthCheck
{
    private int $maximumPercentage;

    public function __construct(int $maximumPercentage)
    {
        $this->maximumPercentage = $maximumPercentage;
    }

    public function getResult(Destination $destination): HealthCheckResult
    {
        // TODO: Implement getResults() method.

        return HealthCheckResult::ok();
    }
}
