<?php

namespace Spatie\BackupServer\Tasks\Monitor\Events;

use Spatie\BackupServer\Models\Destination;

class UnhealthyDestinationFoundEvent
{
    public function __construct(
        public Destination $destination,
        public
        array $failureMessages
    ) {
    }
}
