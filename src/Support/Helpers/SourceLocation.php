<?php

namespace Spatie\BackupServer\Support\Helpers;

class SourceLocation
{
    private array $paths;

    private ?string $sshUser;

    private ?string $host;

    private int $port = 22;

    public function __construct(
        array $paths,
        string $sshUser = null,
        string $host = null,
        int $port = 22
    ) {
        $this->paths = $paths;

        $this->sshUser = $sshUser;

        $this->host = $host;

        $this->port = $port;
    }

    public function getPaths(): array
    {
        return $this->paths;
    }

    public function getPort(): int
    {
        return $this->port;
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
