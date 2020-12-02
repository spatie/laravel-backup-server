<?php

namespace Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks;

use Spatie\BackupServer\Exceptions\BackupFailed;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Support\Helpers\Enums\Task;
use Spatie\BackupServer\Tasks\Backup\Support\PendingBackup;
use Spatie\BackupServer\Tasks\Backup\Support\Rsync\RsyncSummaryOuput;
use Symfony\Component\Process\Process;

class RunBackup implements BackupTask
{
    public function execute(Backup $backup): void
    {
        $backup->logInfo(Task::BACKUP, 'Running backup...');

        $pendingBackup = (new PendingBackup())
            ->from($backup->sourceLocation())
            ->exclude($backup->source->excludes ?? [])
            ->to($backup->destinationLocation())
            ->usePrivateKeyFile($backup->source->ssh_private_key_file ?? '')
            ->reportProgress(function (string $type, string $progress) use ($backup) {
                $backup->handleProgress($type, $progress);
            });

        /** @var \Spatie\BackupServer\Models\Backup $previousCompletedBackup */
        if ($previousCompletedBackup = $backup->source->backups()->completed()->latest()->first()) {
            $pendingBackup->incrementalFrom($previousCompletedBackup->destinationLocation()->getFullPath());
        }

        $this->runBackup($pendingBackup, $backup);
    }

    protected function runBackup(PendingBackup $pendingBackup, Backup $backup)
    {
        $command = $this->getBackupCommand($pendingBackup);

        $progressCallable = $pendingBackup->progressCallable;

        $process = Process::fromShellCommandline($command)->setTimeout(null);

        $rsyncStart = now();
        $process->run(fn (string $type, string $buffer) => $progressCallable($type, $buffer));
        $rsyncEnd = now();

        $backup->update(['rsync_time_in_seconds' => $rsyncStart->diffInSeconds($rsyncEnd)]);

        $didCompleteSuccessFully = $process->getExitCode() === 0;

        $this->saveRsyncSummary($backup, $process->getOutput());

        if (! $didCompleteSuccessFully) {
            throw BackupFailed::rsyncDidFail($backup, $process->getErrorOutput());
        }
    }

    protected function getBackupCommand(PendingBackup $pendingBackup): string
    {
        $source = $pendingBackup->source;

        $port = $pendingBackup->source->getPort();

        $destination = $pendingBackup->destination;

        $linkFromDestination = '';
        if ($pendingBackup->incrementalFromDirectory) {
            $linkFromDestination = "--link-dest {$pendingBackup->incrementalFromDirectory}";
        }

        $privateKeyFile = '';

        if ($pendingBackup->privateKeyFile !== '') {
            $privateKeyFile = "-i {$pendingBackup->privateKeyFile}";
        }

        $excludes = collect($pendingBackup->excludedPaths)
            ->map(fn (string $excludedPath) => "--exclude={$excludedPath}")
            ->implode(' ');

        return "rsync -progress -zaHLK  --stats --info=progress2 {$excludes} {$linkFromDestination} -e \"ssh {$privateKeyFile} -p {$port}\" {$source} {$destination}";
    }

    protected function saveRsyncSummary(Backup $backup, string $output)
    {
        $startingPosition = strpos($output, "Number of files");

        $summary = substr($output, $startingPosition);

        $backup->logInfo(Task::BACKUP, trim($summary));

        $backup->update([
            'rsync_summary' => trim($summary),
            'rsync_average_transfer_speed_in_MB_per_second' => (new RsyncSummaryOuput($output))->averageSpeedInMB(),
        ]);
    }
}
