<?php

namespace Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks;

use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Support\Helpers\Enums\Task;

class CalculateBackupSize implements BackupTask
{
    public function execute(Backup $backup): void
    {
        $backup->logInfo(Task::Backup, 'Calculating backup size...');

        $backup->recalculateBackupSize();

        $backup->source->backups->recalculateRealSizeInKb();
    }
}
