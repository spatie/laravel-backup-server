<?php

namespace Spatie\BackupServer\Tasks\Backup\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Backup;
use Throwable;

class BackupFailedEvent
{
    use Dispatchable, SerializesModels;

    public Backup $backup;

    public string $exceptionMessage;

    public string $trace;

    public function __construct(Backup $backup, Throwable $throwable)
    {
        $this->backup = $backup;

        $this->exceptionMessage = $throwable->getMessage();
        $this->trace = $throwable->getTraceAsString();
    }

    public function getExceptionMessage(): string
    {
        return $this->exceptionMessage;
    }

    public function getTrace(): string
    {
        return $this->trace;
    }
}
