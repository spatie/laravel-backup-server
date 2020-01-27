<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Source;

use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckResult;

abstract class SourceHealthCheck
{
    abstract public function getResult(Source $source): HealthCheckResult;
}
