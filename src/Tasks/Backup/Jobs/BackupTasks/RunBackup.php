<?php

namespace Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks;

use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Support\Enums\Task;
use Spatie\BackupServer\Tasks\Backup\Support\PendingBackup;
use Symfony\Component\Process\Process;

class RunBackup implements BackupTask
{
    public function execute(Backup $backup)
    {
        $backup->logInfo(Task::BACKUP, 'Running backup...');

        $backup->markAsInProgress();

        $pendingBackup = (new PendingBackup())
            ->from($backup->sourceLocation())
            ->exclude($backup->source->excludes ?? [])
            ->to($backup->destinationLocation())
            ->reportProgress(function (string $type, string $progress) use ($backup) {
                $backup->handleProgress($type, $progress);
            });

        /** @var \Spatie\BackupServer\Models\Backup $previousCompletedBackup */
        if ($previousCompletedBackup = $backup->source->backups()->completed()->latest()->first()) {
            $pendingBackup->incrementalFrom($previousCompletedBackup->destinationLocation()->getFullPath());
        }

        $this->runBackup($pendingBackup);
    }

    protected function runBackup(PendingBackup $pendingBackup): bool
    {
        $command = $this->getBackupCommand($pendingBackup);

        $progressCallable = $pendingBackup->progressCallable;

        $process = Process::fromShellCommandline($command)->setTimeout(null);

        $process->run(fn (string $type, string $buffer) => $progressCallable($type, $buffer));

        $didCompleteSuccessFully = $process->getExitCode() === 0;

        return $didCompleteSuccessFully;
    }

    protected function getBackupCommand(PendingBackup $pendingBackup): string
    {
        $source = $pendingBackup->source;

        $destination = $pendingBackup->destination;

        $linkFromDestination = '';
        if ($pendingBackup->incrementalFromDirectory) {
            $linkFromDestination = "--link-dest {$pendingBackup->incrementalFromDirectory}";
        }

        $excludes = collect($pendingBackup->excludedPaths)
            ->map(fn (string $excludedPath) => "--exclude={$excludedPath}")
            ->implode(' ');

        return "rsync -progress -zaHLK  --stats --info=progress2 {$excludes} {$linkFromDestination} -e ssh {$source} {$destination}";
    }
}
