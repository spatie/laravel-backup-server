<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Jobs\Tasks;

use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Cleanup\Strategies\CleanupStrategy;

class DeleteOldBackups implements CleanupTask
{
    public function execute(Source $source): void
    {
        app(CleanupStrategy::class)->deleteOldBackups($source);
    }
}
