<?php


namespace Spatie\BackupServer\Tasks\Cleanup\Strategies;


use Spatie\BackupServer\Models\Source;
use Spatie\Backup\BackupDestination\BackupCollection;

interface CleanupStrategy
{
    public function deleteOldBackups(Source $source);
}
