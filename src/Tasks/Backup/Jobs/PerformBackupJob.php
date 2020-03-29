<?php

namespace Spatie\BackupServer\Tasks\Backup\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Tasks\Backup\Events\BackupCompletedEvent;
use Spatie\BackupServer\Tasks\Backup\Events\BackupFailedEvent;
use Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks\CalculateBackupSize;
use Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks\DetermineDestinationPath;
use Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks\EnsureDestinationIsReachable;
use Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks\EnsureSourceIsReachable;
use Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks\PerformPostBackupCommands;
use Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks\PerformPreBackupCommands;
use Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks\RunBackup;
use Throwable;

class PerformBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Backup $backup;

    public $timeout = 60 * 60;

    public function __construct(Backup $backup)
    {
        $this->backup = $backup;
    }

    public function handle()
    {
        $this->backup->markAsInProgress();

        try {
            $tasks = [
                EnsureSourceIsReachable::class,
                EnsureDestinationIsReachable::class,
                DetermineDestinationPath::class,
                PerformPreBackupCommands::class,
                RunBackup::class,
                PerformPostBackupCommands::class,
                CalculateBackupSize::class,
            ];

            collect($tasks)
                ->map(fn (string $backupTaskClass) => app($backupTaskClass))
                ->each->execute($this->backup);

            $this->backup->markAsCompleted();

            event(new BackupCompletedEvent($this->backup));
        } catch (Throwable $exception) {
            $this->backup->markAsFailed($exception->getMessage());

            event(new BackupFailedEvent($this->backup, $exception));
        }
    }
}
