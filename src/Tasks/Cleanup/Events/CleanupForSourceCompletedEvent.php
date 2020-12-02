<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Source;

class CleanupForSourceCompletedEvent
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public Source $source
    ) {}
}
