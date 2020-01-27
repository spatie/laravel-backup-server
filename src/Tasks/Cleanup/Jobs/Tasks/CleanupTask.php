<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Jobs\Tasks;

use Spatie\BackupServer\Models\Source;

interface CleanupTask
{
    public function execute(Source $source);
}
