<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Support\Helpers\Enums\Task;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForDestinationCompletedEvent;
use Spatie\BackupServer\Tasks\Cleanup\Events\CleanupForDestinationFailedEvent;
use Throwable;

class PerformCleanupDestinationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 60 * 60;

    public Destination $destination;

    public function __construct(Destination $destination)
    {
        $this->destination = $destination;
    }

    public function handle()
    {
        $this->destination->logInfo(Task::CLEANUP, 'Starting cleanup of destination');

        $this->destination->disk()->allDirectories();

        $this->destination->logInfo(Task::CLEANUP, 'Destination cleaned up');

        event(new CleanupForDestinationCompletedEvent($this->destination));
    }

    public function failed(Throwable $exception)
    {
        $this->destination->logError(Task::CLEANUP, "Error while cleaning up destination `{$this->destination->name}`: `{$exception->getMessage()}`");

        event(new CleanupForDestinationFailedEvent($this->destination, $exception->getMessage()));
    }
}
