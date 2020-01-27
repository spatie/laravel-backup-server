<?php

namespace Spatie\BackupServer\Tasks\Backup\Support;

use App\Actions\CreateNewBackupAction;
use Spatie\BackupServer\Support\DestinationLocation;
use Spatie\BackupServer\Support\SourceLocation;

class PendingBackup
{
    public ?SourceLocation $source = null;

    public ?DestinationLocation $destination = null;

    public ?string $incrementalFromDirectory = null;

    public array $excludedPaths = [];

    public $progressCallable;

    public function from(SourceLocation $location): self
    {
        $this->source = $location;

        return $this;
    }

    public function to(DestinationLocation $location): self
    {
        $this->destination = $location;

        return $this;
    }

    public function exclude(array $excludedPaths): self
    {
        $this->excludedPaths = $excludedPaths;

        return $this;
    }

    public function incrementalFrom(string $directory): self
    {
        $this->incrementalFromDirectory = $directory;

        return $this;
    }

    public function reportProgress(callable $progressCallable)
    {
        $this->progressCallable = $progressCallable;

        return $this;
    }

    public function start(): void
    {
        app(CreateNewBackupAction::class)->execute($this);
    }
}
