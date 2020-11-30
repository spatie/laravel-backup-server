<?php

namespace Spatie\BackupServer\Tasks\Backup\Support\BackupScheduler;

use Cron\CronExpression;
use Illuminate\Support\Carbon;
use Spatie\BackupServer\Models\Source;

class DefaultBackupScheduler implements BackupScheduler
{
    public function shouldBackupNow(Source $source): bool
    {
        return $source->next_backup_at->isPast();
    }

    public function scheduleNextBackup(Source $source)
    {
        $source->update([
            'next_backup_at' => CronExpression::factory($source->cron_expression)->getNextRunDate(Carbon::now()),
        ]);
    }
}
