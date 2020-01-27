<?php

namespace Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks;

use Spatie\BackupServer\Models\Backup;

interface BackupTask
{
    public function execute(Backup $backup);
}
