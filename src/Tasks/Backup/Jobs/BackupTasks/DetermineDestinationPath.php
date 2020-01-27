<?php

namespace Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks;

use Illuminate\Support\Facades\File;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Support\Helpers\Enums\Task;

class DetermineDestinationPath implements BackupTask
{
    public function execute(Backup $backup)
    {
        $backup->logInfo(Task::BACKUP, 'Determining destination directory...');

        $directory = $backup->destination->directory . $backup->source->id . '/backup-' . $backup->created_at->format('Y-m-d-His') . '/';

        $pathPrefix = $backup->disk()->getDriver()->getAdapter()->getPathPrefix();

        $fullDirectory = $pathPrefix . $directory;

        File::makeDirectory($fullDirectory, 0755, true);

        $backup->update(['path' => $directory]);
    }
}
