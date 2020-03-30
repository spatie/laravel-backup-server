<?php


namespace Spatie\BackupServer\Models\Concerns;

trait HasAsyncDelete
{
    public function asyncDelete(): void
    {
        $this->update(['status' => static::STATUS_DELETING]);

        $deletionJobClassName = $this->getDeletionJobClassName();

        dispatch(new $deletionJobClassName($this));
    }

    abstract public function getDeletionJobClassName(): string;
}
