<?php

namespace Spatie\BackupServer\Tasks\Backup\Jobs;

use App\Actions\CreateNewBackupAction;
use Spatie\BackupServer\Support\Enums\Task;
use Spatie\BackupServer\Tasks\Backup\Events\BackupCompletedEvent;
use Spatie\BackupServer\Tasks\Backup\Events\BackupFailedEvent;
use Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks\BackupTask;
use Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks\CalculateBackupSize;
use Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks\DetermineDestinationPath;
use Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks\EnsureSourceIsReachable;
use Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks\PerformCleanup;
use Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks\PerformPostBackupCommands;
use Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks\PerformPreBackupCommands;
use Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks\RunBackup;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Backup\Support\PendingBackup;
use Spatie\BackupServer\Support\ReportProgress;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class PerformBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Backup $backup;

    public function __construct(Backup $backup)
    {
        $this->backup = $backup;
    }

    public function handle()
    {
        $tasks = [
            EnsureSourceIsReachable::class,
            DetermineDestinationPath::class,
            PerformPreBackupCommands::class,
            RunBackup::class,
            PerformPostBackupCommands::class,
            CalculateBackupSize::class,
        ];

        collect($tasks)
            ->map(fn(string $backupTaskClass) => app($backupTaskClass))
            ->each->execute($this->backup);

        $this->backup->markAsCompleted();

        event(new BackupCompletedEvent($this->backup));
    }

    public function failed(Throwable $exception)
    {
        $this->backup->markAsFailed($exception->getMessage());

        event(new BackupFailedEvent($this->backup, $exception));
    }
}
