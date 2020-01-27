<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks;

abstract class HealthCheck
{
    public function name()
    {
        return Str::title(class_basename($this));
    }
}
