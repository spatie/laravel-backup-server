<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Jobs\Tasks;

use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Support\Helpers\Enums\Task;

class DeleteFailedBackups implements CleanupTask
{
    public function execute(Source $source): void
    {
        $source->backups()
            ->failed()
            ->get()
            ->filter(fn (Backup $backup) => $backup->created_at->diffInDays() > 1)
            ->each(function (Backup $backup) use ($source) {
                $source->logInfo(Task::Cleanup, "Removing backup id {$backup->id} because it has failed");

                $backup->delete();
            });
    }
}
