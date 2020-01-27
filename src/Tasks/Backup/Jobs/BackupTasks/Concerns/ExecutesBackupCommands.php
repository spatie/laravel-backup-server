<?php

namespace Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks\Concerns;

use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Support\Enums\Task;

trait ExecutesBackupCommands
{
    public function executeBackupCommands(Backup $backup, string $commandAttributeName)
    {
        $commands = $backup->source->$commandAttributeName ?? [];

        if (! count($commands)) {
            return;
        }

        $label = str_replace('_', ' ', $commandAttributeName);

        $backup->logInfo(Task::BACKUP, "Performing {$label}...");

        $process = $backup->source->executeSshCommands($commands);

        if (! $process->isSuccessful()) {
            $backup->logError(Task::BACKUP, $label . ' error output:' . PHP_EOL .$process->getOutput());
            return;
        }

        $backup->logInfo(Task::BACKUP, $label . ' output:' . PHP_EOL .$process->getOutput());
    }
}
