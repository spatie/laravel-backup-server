<?php

namespace Spatie\BackupServer\Tasks\Monitor\Events;

use Spatie\BackupServer\Models\Destination;

class HealthyDestinationFound
{
    public Destination $destination;

    public function __construct(Destination $destination)
    {
        $this->destination = $destination;
    }
}
