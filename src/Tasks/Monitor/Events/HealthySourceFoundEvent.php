<?php

namespace Spatie\BackupServer\Tasks\Monitor\Events;

use Spatie\BackupServer\Models\Source;

class HealthySourceFoundEvent
{
    public Source $source;

    public function __construct(Source $source)
    {
        $this->source = $source;
    }
}
