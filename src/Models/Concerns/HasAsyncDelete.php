<?php

namespace Spatie\BackupServer\Models\Concerns;

use Spatie\BackupServer\Enums\BackupStatus;
use Spatie\BackupServer\Enums\DestinationStatus;
use Spatie\BackupServer\Enums\SourceStatus;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Models\Source;

trait HasAsyncDelete
{
    public function asyncDelete(): void
    {
        $this->update(['status' => $this->status()]);

        $deletionJobClassName = $this->getDeletionJobClassName();

        dispatch(new $deletionJobClassName($this));
    }

    public function willBeDeleted(): bool
    {
        return $this->status === $this->status();
    }

    abstract public function getDeletionJobClassName(): string;

    protected function status(): DestinationStatus|BackupStatus|SourceStatus
    {
        return match (static::class) {
            Source::class => SourceStatus::Deleting,
            Destination::class => DestinationStatus::Deleting,
            Backup::class => BackupStatus::Deleting,
            default => throw new \InvalidArgumentException(
                'Unknown class type for deletion status: '.static::class
            ),
        };
    }
}
