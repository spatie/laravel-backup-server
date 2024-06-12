<?php

namespace Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks;

use Illuminate\Support\Facades\File;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Support\Helpers\Enums\Task;

class DetermineDestinationPath implements BackupTask
{
    public function execute(Backup $backup): void
    {
        $backup->logInfo(Task::Backup, 'Determining destination directory...');

        $directory = $backup->destination->directory.$backup->source->id.'/backup-'.$backup->created_at->format('Y-m-d-His').'/';

        $pathPrefix = $backup->pathPrefix();

        $fullDirectory = $pathPrefix.'/'.$directory;

        if (! file_exists($fullDirectory)) {
            File::makeDirectory($fullDirectory, 0755, true);
        }

        $backup->update(['path' => $directory]);
    }
}
