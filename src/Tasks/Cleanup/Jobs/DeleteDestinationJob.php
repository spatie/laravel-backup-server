<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Destination;

class DeleteDestinationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Destination $destination;

    public function __construct(Destination $destination)
    {
        $this->destination = $destination;
    }

    public function handle()
    {
        $this->destination->backups->each(
            fn (Backup $backup) => $backup->delete()
        );

        $this->destination->delete();
    }
}
