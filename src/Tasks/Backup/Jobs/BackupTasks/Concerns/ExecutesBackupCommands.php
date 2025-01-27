<?php

namespace Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks\Concerns;

use Spatie\BackupServer\Exceptions\BackupFailed;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Support\Helpers\Enums\Task;

trait ExecutesBackupCommands
{
    public function executeBackupCommands(Backup $backup, string $commandAttributeName): void
    {
        $commands = $backup->source->$commandAttributeName ?? [];

        if (! count($commands)) {
            return;
        }

        $label = str_replace('_', ' ', $commandAttributeName);

        $backup->logInfo(Task::Backup, "Performing {$label}...");

        /** @var \Symfony\Component\Process\Process $process */
        $process = $backup->source->executeSshCommands($commands);

        if (! $process->isSuccessful()) {
            $backup->logError(Task::Backup, $label.' error output:'.PHP_EOL.$process->getErrorOutput());

            throw BackupFailed::BackupCommandsFailed($backup, $commandAttributeName, $process->getErrorOutput());
        }

        $backup->logInfo(Task::Backup, $label.' output:'.PHP_EOL.$process->getOutput());
    }
}
