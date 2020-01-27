<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Jobs;

use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Support\Enums\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PerformCleanupDestinationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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

    }
}
