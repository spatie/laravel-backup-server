<?php

namespace Spatie\BackupServer\Tasks\Backup\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Backup;

class BackupCompletedEvent
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public Backup $backup
    ) {}
}
