<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Source;

use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckResult;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\HealthCheck;

abstract class SourceHealthCheck extends HealthCheck
{
    abstract public function getResult(Source $source): HealthCheckResult;
}
