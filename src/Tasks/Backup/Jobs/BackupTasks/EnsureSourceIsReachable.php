<?php

namespace Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks;

use App\Exceptions\BackupFailed;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Support\Enums\Task;

class EnsureSourceIsReachable implements BackupTask
{
    public function execute(Backup $backup)
    {
        $backup->logInfo(Task::BACKUP, 'Ensuring source is reachable...');

        $process = $backup->source->executeSshCommands(['whoami']);

        if (! $process->isSuccessful()) {
            throw BackupFailed::sourceNotReachable($backup, $process->getErrorOutput());
        }
    }
}
