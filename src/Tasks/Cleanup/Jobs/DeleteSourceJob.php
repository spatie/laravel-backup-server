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

    private Source $source;

    public function __construct(Source $source)
    {
        $this->source = $source;

        $this->timeout = config('backup-server.jobs.delete_source_job.timeout');

        $this->queue = config('backup-server.jobs.delete_source_job.queue');

        $this->connection = $this->connection ?? Config::getQueueConnection();
    }

    public function handle()
    {
        $this->source->backups->each(
            fn (Backup $backup) => $backup->delete()
        );

        $this->source->delete();
    }
}
