<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Spatie\BackupServer\Models\Backup;

class DeleteBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Backup $backup;

    public function __construct(Backup $backup)
    {
        $this->backup = $backup;

        $this->timeout = config('backup-server.jobs.delete_backup_job.timeout');

        $this->queue = config('backup-server.jobs.delete_backup_job.queue');
    }

    public function handle()
    {
        $this->backup->delete();
    }
}
