<?php

namespace Spatie\BackupServer\Models\Concerns;

use Spatie\BackupServer\Enums\BackupStatus;
use Spatie\BackupServer\Enums\SourceStatus;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Models\Source;

trait HasAsyncDelete
{
    public function asyncDelete(): void
    {
        $this->update(['status' => $this->status($this)]);

        $deletionJobClassName = $this->getDeletionJobClassName();

        dispatch(new $deletionJobClassName($this));
    }

    public function willBeDeleted(): bool
    {
        return $this->status === $this->status($this);
    }

    abstract public function getDeletionJobClassName(): string;

    protected function status(self $class)
    {
        return match ($class::class) {
            Source::class => SourceStatus::Deleting,
            Destination::class => Destination::STATUS_DELETING,
            Backup::class => BackupStatus::Deleting,
            default => throw new \Exception('Unknown class type'),
        };
    }
}
