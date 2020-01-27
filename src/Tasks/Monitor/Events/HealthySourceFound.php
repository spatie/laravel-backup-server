<?php

namespace Spatie\BackupServer\Tasks\Monitor\Events;

use Spatie\BackupServer\Models\Source;

class HealthySourceFound
{
    public Source $source;

    public function __construct(Source $source)
    {
        $this->source = $source;
    }
}
