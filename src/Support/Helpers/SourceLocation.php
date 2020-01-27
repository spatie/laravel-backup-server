<?php

namespace Spatie\BackupServer\Support\Helpers;

class SourceLocation
{
    private array $paths;

    private ?string $sshUser;

    private ?string $host;

    public function __construct(array $paths, string $sshUser = null, string $host = null)
    {
        $this->paths = $paths;

        $this->sshUser = $sshUser;

        $this->host = $host;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function connectionString(): string
    {
        return "{$this->sshUser}@{$this->host}";
    }

    public function __toString()
    {
        $remotePart = "{$this->connectionString()}:";

        return collect($this->paths)
            ->map(fn (string $path) => rtrim($path, '/'))
            ->map(fn (string $path) => $remotePart .$path)
            ->implode(' ');
    }
}
