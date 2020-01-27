<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination;

use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckResult;

abstract class DestinationHealthCheck
{
    abstract public function getResults(Destination $destination): HealthCheckResult;
}
