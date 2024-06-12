<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Support\Helpers\Config;

class DeleteSourceJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private Source $source)
    {
        $this->timeout = config('backup-server.jobs.delete_source_job.timeout');

        $this->queue = config('backup-server.jobs.delete_source_job.queue');

        $this->connection ??= Config::getQueueConnection();
    }

    public function handle(): void
    {
        $this->source->backups->each(
            fn (Backup $backup) => $backup->delete()
        );

        $this->source->delete();
    }
}
