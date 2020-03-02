<?php

namespace Spatie\BackupServer\Tasks\Backup\Actions;

use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Backup\Jobs\PerformBackupJob;

class CreateBackupAction
{
    public function execute(Source $source): Backup
    {
        /** @var \Spatie\BackupServer\Models\Backup $backup */
        $backup = Backup::create([
            'status' => Backup::STATUS_PENDING,
            'source_id' => $source->id,
            'destination_id' => $source->destination->id,
            'disk' => $source->destination->disk,
        ]);

        dispatch(new PerformBackupJob($backup));

        return $backup;
    }
}
