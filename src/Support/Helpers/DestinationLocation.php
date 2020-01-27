<?php

namespace Spatie\BackupServer\Support\Helpers;

use Illuminate\Contracts\Filesystem\Filesystem;

class DestinationLocation
{
    private Filesystem $disk;

    private string $path;

    public function __construct(Filesystem $disk, string $path)
    {
        $this->disk = $disk;

        $this->path = $path;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getDirectory(): string
    {
        return pathinfo($this->getPath(), PATHINFO_BASENAME);
    }

    public function getFullPath(): string
    {
        $pathPrefix = $this->disk->getDriver()->getAdapter()->getPathPrefix();

        return $pathPrefix  . $this->path;
    }

    public function __toString()
    {
        return $this->getFullPath();
    }
}
