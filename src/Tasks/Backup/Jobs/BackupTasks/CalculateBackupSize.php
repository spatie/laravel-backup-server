<?php

namespace Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks;

use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Support\Helpers\Enums\Task;

class CalculateBackupSize implements BackupTask
{
    public function execute(Backup $backup)
    {
        $backup->logInfo(Task::BACKUP, 'Calculating backup size...');

        $backup->recalculateBackupSize();
        $backup->recalculateRealBackupSize();

        $backup->source->completedBackups->each(fn (Backup $backup) => $backup->recalculateRealBackupSize());
    }
}
