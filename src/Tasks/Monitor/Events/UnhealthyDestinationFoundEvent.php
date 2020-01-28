<?php

namespace Spatie\BackupServer\Tasks\Monitor\Events;

use Spatie\BackupServer\Models\Destination;

class UnhealthyDestinationFoundEvent
{
    public Destination $destination;

    public array $failureMessages;

    public function __construct(Destination $destination, array $failureMessages)
    {
        $this->destination = $destination;

        $this->failureMessages = $failureMessages;
    }
}
