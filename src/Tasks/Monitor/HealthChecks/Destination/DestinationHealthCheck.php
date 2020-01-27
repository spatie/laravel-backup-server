<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination;

use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckResult;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\HealthCheck;

abstract class DestinationHealthCheck extends HealthCheck
{
    abstract public function getResult(Destination $destination): HealthCheckResult;
}
