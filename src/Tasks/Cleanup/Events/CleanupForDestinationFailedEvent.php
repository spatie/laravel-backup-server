<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Destination;

class CleanupForDestinationFailedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Destination $destination,
        public string $exceptionMessage
    ) {}
}
