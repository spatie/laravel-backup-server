<?php

namespace Spatie\BackupServer\Tasks\Backup\Support\FileList;

use Spatie\BackupServer\Models\Backup;
use Symfony\Component\Finder\Finder;

class FileList
{
    private Backup $backup;

    private string $relativePath;

    public function __construct(Backup $backup, string $relativePath)
    {
        $this->backup = $backup;

        $this->relativePath = $relativePath;
    }

    public function entries(): array
    {
        $backupBasePath = $this->backup->destinationLocation()->getFullPath();

        $fileListingPath = $backupBasePath . $this->relativePath;

        $entries = [];

        $finder = (new Finder())
            ->directories()
            ->in($fileListingPath)
            ->depth(0)
            ->sortByName()
            ->getIterator();
        foreach ($finder as $file) {
            $entries[] = new FileListEntry($file, $backupBasePath);
        }

        $finder = (new Finder())
            ->files()
            ->in($fileListingPath)
            ->depth(0)
            ->sortByName()
            ->getIterator();
        foreach ($finder as $file) {
            $entries[] = new FileListEntry($file, $backupBasePath);
        }

        return $entries;
    }
}
