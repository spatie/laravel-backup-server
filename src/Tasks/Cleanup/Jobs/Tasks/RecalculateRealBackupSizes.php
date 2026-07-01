<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Jobs\Tasks;

use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Support\Helpers\Enums\Task;
use Spatie\BackupServer\Tasks\Backup\Support\BackupCollection;

class RecalculateRealBackupSizes implements CleanupTask
{
    public function execute(Source $source): void
    {
        $source->logInfo(Task::Cleanup, 'Recalculating real backup sizes...');

        /** @var BackupCollection $backupCollection */
        $backupCollection = $source->completedBackups()->get();

        $backupCollection->recalculateRealSizeInKb();
    }
}
