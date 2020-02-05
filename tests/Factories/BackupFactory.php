<?php

namespace Spatie\BackupServer\Tests\Factories;

use Illuminate\Support\Facades\File;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Models\Source;

class BackupFactory
{
    private ?Source $source;

    private ?Destination $destination;

    private bool $createBackupDirectory = false;

    private array $files = [];

    private ?string $status = null;

    public function source(Source $source): self
    {
        $this->source = $source;

        return $this;
    }

    public function completed(): self
    {
        $this->status = Backup::STATUS_COMPLETED;

        return $this;
    }

    public function destination(Destination $destination): self
    {
        $this->destination = $destination;

        return $this;
    }

    public function makeSureBackupDirectoryExists($createBackupDirectory = true): self
    {
        $this->createBackupDirectory = $createBackupDirectory;

        return $this;
    }

    public function addFiles(array $files): self
    {
        $this->makeSureBackupDirectoryExists();

        $this->files = array_merge($files, $this->files);

        return $this;
    }

    public function create(array $attributes = []): Backup
    {
        $this->source ??= factory(Source::class)->create();
        $this->destination ??= factory(Destination::class)->create();

        $attributes = array_merge([
            'source_id' => $this->source->id,
            'destination_id' => $this->destination->id,
        ], $attributes);

        if ($this->status) {
            $attributes['status'] = $this->status;
        }

        /** @var \Spatie\BackupServer\Models\Backup $backup */
        $backup = factory(Backup::class)->create($attributes);

        if ($this->createBackupDirectory) {
            $backup->disk()->makeDirectory($backup->destinationLocation()->getPath());

            collect($this->files)->each(function (string $filePath) use ($backup) {
                $destination = $backup->destinationLocation()->getFullPath() .'/' . pathinfo($filePath, PATHINFO_BASENAME);

                File::copy($filePath, $destination);
            });
        }

        return $backup;
    }
}
