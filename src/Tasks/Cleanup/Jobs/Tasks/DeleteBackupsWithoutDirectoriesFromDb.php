<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Jobs\Tasks;

use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Support\Helpers\Enums\Task;

class DeleteBackupsWithoutDirectoriesFromDb implements CleanupTask
{
    public function execute(Source $source)
    {
        $source
            ->completedBackups()
            ->each(function (Backup $backup) {
                if (! $backup->existsOnDisk()) {
                    $backup->source->logInfo(Task::CLEANUP, "Removing backup id `{$backup->id}` because its directory `{$backup->destinationLocation()->getPath()}` on disk `{$backup->disk_name}` does not exist anymore ");
                    $backup->delete();
                }
            });
    }
}
