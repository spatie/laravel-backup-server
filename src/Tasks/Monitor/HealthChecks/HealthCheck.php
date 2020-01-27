<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks;

use Illuminate\Support\Str;

abstract class HealthCheck
{
    public function name()
    {
        return Str::title(class_basename($this));
    }
}
