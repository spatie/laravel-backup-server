<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Source;

class CleanupForSourceFailedEvent
{
    use Dispatchable, SerializesModels;

    public Source $source;

    public string $exceptionMessage;

    public function __construct(Source $source, string $exceptionMessage)
    {
        $this->source = $source;

        $this->exceptionMessage = $exceptionMessage;
    }
}
