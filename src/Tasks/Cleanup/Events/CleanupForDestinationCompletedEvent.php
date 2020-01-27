<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Destination;

class CleanupForDestinationCompletedEvent
{
    use Dispatchable, SerializesModels;

    public Destination $destination;

    public function __construct(Destination $destination)
    {
        $this->destination = $destination;
    }
}
