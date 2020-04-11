<?php

namespace Spatie\BackupServer\Tasks\Backup\Support\BackupScheduler;

use Spatie\BackupServer\Models\Source;

class DefaultBackupScheduler implements BackupScheduler
{
    public function shouldBackupNow(Source $source): bool
    {
        return now()->hour === $source->backup_hour;
    }
}
