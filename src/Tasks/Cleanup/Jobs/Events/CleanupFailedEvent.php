<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Jobs\Events;

use Spatie\BackupServer\Models\Source;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Throwable;

class CleanupFailedEvent
{
    use Dispatchable, SerializesModels;

    public Source $source;

    public Throwable $throwable;

    public function __construct(Source $source, Throwable $throwable)
    {
        $this->source = $source;

        $this->throwable = $throwable;
    }
}
