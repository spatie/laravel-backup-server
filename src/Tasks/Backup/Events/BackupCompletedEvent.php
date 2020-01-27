<?php

namespace Spatie\BackupServer\Tasks\Backup\Events;

use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Source;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BackupCompletedEvent
{
    use Dispatchable, SerializesModels;

    public Backup $backup;

    public function __construct(Backup $backup)
    {
        $this->backup = $backup;
    }
}
