<?php

namespace Spatie\BackupServer\Models\Concerns;

use Spatie\BackupServer\Enums\BackupStatus;

trait HasAsyncDelete
{
    public function asyncDelete(): void
    {
        $this->update(['status' => BackupStatus::Deleting]);

        $deletionJobClassName = $this->getDeletionJobClassName();

        dispatch(new $deletionJobClassName($this));
    }

    public function willBeDeleted(): bool
    {
        return $this->status === BackupStatus::Deleting;
    }

    abstract public function getDeletionJobClassName(): string;
}
