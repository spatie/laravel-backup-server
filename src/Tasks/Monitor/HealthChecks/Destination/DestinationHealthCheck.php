<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination;

use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckResponse;

abstract class DestinationHealthCheck
{
    abstract public function passes(Destination $destination): HealthCheckResponse;
}
