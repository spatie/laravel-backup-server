<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Source;

use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckResponse;

abstract class SourceHealthCheck
{
    abstract public function passes(Source $source): HealthCheckResponse;
}
