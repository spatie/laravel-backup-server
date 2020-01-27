<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Jobs\Tasks;

use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Support\Enums\Task;

class RecalculateRealBackupSizes implements CleanupTask
{
public function execute(Source $source)
{
    $source->logInfo(Task::CLEANUP, 'Recalculating real backup sizes...');

    $source->completedBackups()->each(fn(Backup $backup) => $backup->recalculateRealBackupSize());
}}
