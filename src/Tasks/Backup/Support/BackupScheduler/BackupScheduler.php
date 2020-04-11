<?php

namespace Spatie\BackupServer\Tasks\Backup\Support\BackupScheduler;

use Spatie\BackupServer\Models\Source;

interface BackupScheduler
{
    public function shouldBackupNow(Source $source): bool;
}
