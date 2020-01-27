<?php

namespace Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks;

use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks\Concerns\ExecutesBackupCommands;

class PerformPreBackupCommands implements BackupTask
{
    use ExecutesBackupCommands;

    public function execute(Backup $backup)
    {
        $this->executeBackupCommands($backup, 'pre_backup_commands');

    }
}
