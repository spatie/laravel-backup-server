<?php

namespace Spatie\BackupServer\Tasks\Backup\Support\BackupScheduler;

use Cron\CronExpression;
use Illuminate\Support\Carbon;
use Spatie\BackupServer\Models\Source;

class DefaultBackupScheduler implements BackupScheduler
{
    public function shouldBackupNow(Source $source): bool
    {
        return CronExpression::factory($source->cron_expression)->isDue(Carbon::now());
    }
}
