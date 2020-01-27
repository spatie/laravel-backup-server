<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Destination;
use Throwable;

class CleanupForDestinationFailedEvent
{
    use Dispatchable, SerializesModels;

    public Destination $destination;

    public Throwable $throwable;

    public function __construct(Destination $destination, Throwable $throwable)
    {
        $this->destination = $destination;

        $this->throwable = $throwable;
    }
}
