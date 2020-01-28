<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination;

use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckResult;

class DestinationReachable extends DestinationHealthCheck
{
    public function getResult(Destination $destination): HealthCheckResult
    {
        return $destination->reachable()
            ? HealthCheckResult::ok()
            : HealthCheckResult::failed('Could not reach destination')->doNotRunRemainingChecks();
    }
}
