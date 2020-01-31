<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Destination;

class CleanupForDestinationFailedEvent
{
    use Dispatchable, SerializesModels;

    public Destination $destination;

    public string $exceptionMessage;

    public function __construct(Destination $destination, string $exceptionMessage)
    {
        $this->destination = $destination;

        $this->exceptionMessage = $exceptionMessage;
    }
}
