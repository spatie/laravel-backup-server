<?php

namespace Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks;

use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Tasks\Backup\Jobs\BackupTasks\Concerns\ExecutesBackupCommands;

class PerformPostBackupCommands implements BackupTask
{
    use ExecutesBackupCommands;

    public function execute(Backup $backup)
    {
        $this->executeBackupCommands($backup, 'post_backup_commands');
    }
}
