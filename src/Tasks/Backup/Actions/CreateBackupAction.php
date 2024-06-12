<?php

namespace Spatie\BackupServer\Tasks\Backup\Actions;

use Spatie\BackupServer\Enums\BackupStatus;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Support\Helpers\Enums\Task;
use Spatie\BackupServer\Tasks\Backup\Jobs\PerformBackupJob;

class CreateBackupAction
{
    protected $dispatchOnQueue = true;

    /** @var callable|null */
    protected $afterBackupModelCreated;

    public function afterBackupModelCreated(callable $afterBackupModelCreated): self
    {
        $this->afterBackupModelCreated = $afterBackupModelCreated;

        return $this;
    }

    public function doNotUseQueue(): self
    {
        $this->dispatchOnQueue = false;

        return $this;
    }

    public function execute(Source $source): Backup
    {
        $backup = Backup::create([
            'status' => BackupStatus::Pending,
            'source_id' => $source->id,
            'destination_id' => $source->destination->id,
            'disk_name' => $source->destination->disk_name,
        ]);

        if ($this->afterBackupModelCreated) {
            ($this->afterBackupModelCreated)($backup);
        }

        $backup->logInfo(Task::Backup, 'Dispatching backup job...');

        $job = (new PerformBackupJob($backup));

        $this->dispatchOnQueue
            ? dispatch($job)
            : dispatch_sync($job);

        return $backup;
    }
}
