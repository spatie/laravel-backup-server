<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Source;

class CleanupForSourceCompletedEvent
{
    use Dispatchable, SerializesModels;

    public Source $source;

    public function __construct(Source $source)
    {
        $this->source = $source;
    }
}
