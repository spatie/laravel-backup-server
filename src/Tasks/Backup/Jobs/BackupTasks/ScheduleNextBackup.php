<?php

namespace Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks;

use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Tasks\Backup\Support\BackupScheduler\BackupScheduler;

class ScheduleNextBackup implements BackupTask
{
    public function execute(Backup $backup)
    {
        $backupScheduler = app(BackupScheduler::class);

        $backupScheduler->scheduleNextBackup($backup->source);
    }
}
