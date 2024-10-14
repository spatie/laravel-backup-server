<?php

namespace Spatie\BackupServer\Tasks\Monitor\Events;

use Spatie\BackupServer\Models\Source;

class UnhealthySourceFoundEvent
{
    public function __construct(
        public Source $source,
        public array $failureMessages,
    ) {}
}
