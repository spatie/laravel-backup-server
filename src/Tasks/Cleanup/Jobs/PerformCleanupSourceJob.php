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

class PerformCleanupSourceJob implements ShouldQueue
{
    /**
     * @var mixed
     */
    public $timeout;

    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Source $source)
    {
        $this->timeout = config('backup-server.jobs.perform_cleanup_for_source_job.timeout');

        $this->queue = config('backup-server.jobs.perform_cleanup_for_source_job.queue');
    }

    public function handle(): void
    {
        $this->source->logInfo(Task::Cleanup, 'Starting cleanup...');

        $tasks = [
            DeleteBackupsWithoutDirectoriesFromDb::class,
            DeleteOldBackups::class,
            DeleteFailedBackups::class,
            RecalculateRealBackupSizes::class,
        ];

        collect($tasks)
            ->map(fn (string $className) => app($className))
            ->each(fn (CleanupTask $cleanupTask) => $cleanupTask->execute($this->source));

        event(new CleanupForSourceCompletedEvent($this->source));

        $this->source->logInfo(Task::Cleanup, 'Cleanup done!');
    }

    public function failed(Throwable $exception): void
    {
        $this->source->logError(Task::Cleanup, "Error while cleaning up source `{$this->source->name}`: `{$exception->getMessage()}`");

        report($exception);

        event(new CleanupForSourceFailedEvent($this->source, $exception->getMessage()));
    }
}
