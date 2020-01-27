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

    public Throwable $throwable;

    public function __construct(Backup $backup, Throwable $throwable)
    {
        $this->backup = $backup;

        $this->throwable = $throwable;
    }
}
