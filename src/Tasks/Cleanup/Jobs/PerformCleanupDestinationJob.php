<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Support\Helpers\Config;
use Spatie\BackupServer\Support\Helpers\Enums\Task;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForDestinationCompletedEvent;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForDestinationFailedEvent;
use Throwable;

class PerformCleanupDestinationJob implements ShouldQueue
{
    /**
     * @var mixed
     */
    public $timeout;

    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public Destination $destination)
    {
        $this->timeout = config('backup-server.jobs.perform_cleanup_for_destination_job.timeout');

        $this->queue = config('backup-server.jobs.perform_cleanup_for_destination_job.queue');

        $this->connection ??= Config::getQueueConnection();
    }

    public function handle(): void
    {
        $this->destination->logInfo(Task::Cleanup, 'Starting cleanup of destination');

        // TODO: implement

        $this->destination->logInfo(Task::Cleanup, 'Destination cleaned up');

        event(new CleanupForDestinationCompletedEvent($this->destination));
    }

    public function failed(Throwable $exception): void
    {
        $this->destination->logError(Task::Cleanup, "Error while cleaning up destination `{$this->destination->name}`: `{$exception->getMessage()}`");

        event(new CleanupForDestinationFailedEvent($this->destination, $exception->getMessage()));
    }
}
