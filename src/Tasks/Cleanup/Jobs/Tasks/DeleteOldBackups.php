<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Jobs\Tasks;

use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Cleanup\Strategies\CleanupStrategy;
use Spatie\BackupServer\Tasks\Cleanup\Strategies\DefaultCleanupStrategy;

class DeleteOldBackups implements CleanupTask
{
    public function execute(Source $source)
    {
        $this->getStrategy()->deleteOldBackups($source);
    }

    protected function getStrategy(): CleanupStrategy
    {
        return new DefaultCleanupStrategy();
    }
}
