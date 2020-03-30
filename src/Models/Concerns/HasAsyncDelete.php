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

    public function willBeDeleted(): bool
    {
        return $this->status === static::STATUS_DELETING;
    }

    abstract public function getDeletionJobClassName(): string;
}
