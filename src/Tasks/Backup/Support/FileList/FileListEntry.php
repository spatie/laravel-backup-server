<?php

namespace Spatie\BackupServer\Tasks\Backup\Support\FileList;

use Illuminate\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class FileListEntry
{
    private SplFileInfo $file;

    private string $relativeBashPath;

    public function __construct(SplFileInfo $file, string $relativeBashPath)
    {
        $this->file = $file;

        $this->relativeBashPath = $relativeBashPath;
    }

    public function name(): string
    {
        return $this->file->getFilename();
    }

    public function relativePath(): string
    {
        $relativePath = Str::after($this->file->getPathname(), $this->relativeBashPath);

        return Str::start($relativePath, '/');
    }

    public function isDirectory(): bool
    {
        return $this->file->isDir();
    }

    public function size(): int
    {
        return $this->file->getSize();
    }
}
