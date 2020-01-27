<?php


namespace Spatie\BackupServer\Tasks\Cleanup\Strategies;

use Spatie\BackupServer\Models\Source;

interface CleanupStrategy
{
    public function deleteOldBackups(Source $source);
}
