<?php

namespace Spatie\BackupServer\Tasks\Backup\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Backup;

class BackupCompletedEvent
{
    use Dispatchable, SerializesModels;

    public Backup $backup;

    public function __construct(Backup $backup)
    {
        $this->backup = $backup;
    }
}
