<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Jobs;

use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Support\Enums\Task;
use Spatie\BackupServer\Tasks\Backup\Events\BackupFailedEvent;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\Events\CleanupCompletedEvent;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\Events\CleanupFailedEvent;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\Tasks\CleanupTask;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\Tasks\DeleteBackupsWithoutDirectoriesFromDb;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\Tasks\DeleteDirectoriesWithoutDbEntries;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\Tasks\DeleteFailedBackups;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\Tasks\DeleteOldBackups;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\Tasks\RecalculateRealBackupSizes;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

class PerformCleanupBackupsForSourceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var \Spatie\BackupServer\Models\Source */
    public Source $source;

    public function __construct(Source $source)
    {
        $this->source = $source;
    }

    public function handle()
    {
        $this->source->logInfo(Task::CLEANUP, "Starting cleanup...");

        $tasks = [
            DeleteBackupsWithoutDirectoriesFromDb::class,
            DeleteOldBackups::class,
            DeleteFailedBackups::class,
            RecalculateRealBackupSizes::class,
        ];

        collect($tasks)
            ->map(fn(string $className) => app($className))
            ->each(fn(CleanupTask $cleanupTask) => $cleanupTask->execute($this->source));

        event(new CleanupCompletedEvent($this->source));

        $this->source->logInfo(Task::CLEANUP, "Cleanup done!");

    }

    public function failed(Throwable $exception)
    {
        $this->source->logError(Task::CLEANUP, "Error while cleaning up: `{$exception->getMessage()}`");

        event(new CleanupFailedEvent($this->source, $exception));
    }
}
