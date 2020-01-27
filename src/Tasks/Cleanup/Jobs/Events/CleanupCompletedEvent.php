<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Jobs\Events;

use Spatie\BackupServer\Models\Source;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CleanupCompletedEvent
{
    use Dispatchable, SerializesModels;

    public Source $source;

    public function __construct(Source $source)
    {
        $this->source = $source;
    }
}
