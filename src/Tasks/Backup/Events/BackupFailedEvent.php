<?php

namespace Spatie\BackupServer\Tasks\Backup\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Backup;

class BackupFailedEvent
{
    use Dispatchable, SerializesModels;

    public Backup $backup;

    public string $exceptionMessage;

    public function __construct(Backup $backup, string $exceptionMessage)
    {
        $this->backup = $backup;

        $this->exceptionMessage = $exceptionMessage;
    }
}
