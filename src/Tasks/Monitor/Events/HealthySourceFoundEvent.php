<?php

namespace Spatie\BackupServer\Tasks\Monitor\Events;

use Spatie\BackupServer\Models\Source;

class HealthySourceFoundEvent
{
    public function __construct(
        public Source $source
    ) {
    }
}
