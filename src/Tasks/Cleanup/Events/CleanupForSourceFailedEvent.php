<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Source;
use Throwable;

class CleanupForSourceFailedEvent
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
