<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Support\Helpers\Enums\Task;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForSourceCompletedEvent;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForSourceFailedEvent;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\Tasks\CleanupTask;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\Tasks\DeleteBackupsWithoutDirectoriesFromDb;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\Tasks\DeleteFailedBackups;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\Tasks\DeleteOldBackups;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\Tasks\RecalculateRealBackupSizes;
use Throwable;

class PerformCleanupBackupsForSourceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60 * 60;

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
            ->map(fn (string $className) => app($className))
            ->each(function (CleanupTask $cleanupTask) {
                info('starting ' . get_class($cleanupTask));
                $cleanupTask->execute($this->source);
                info('ended ' . get_class($cleanupTask));
            });

        event(new CleanupForSourceCompletedEvent($this->source));

        $this->source->logInfo(Task::CLEANUP, "Cleanup done!");
    }

    public function failed(Throwable $exception)
    {
        $this->source->logError(Task::CLEANUP, "Error while cleaning up source `{$this->source->name}`: `{$exception->getMessage()}`");

        report($exception);

        event(new CleanupForSourceFailedEvent($this->source, $exception->getMessage()));
    }
}
