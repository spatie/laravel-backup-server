<?php

namespace Spatie\BackupServer\Support\Helpers;

class SourceLocation implements \Stringable
{
    public function __construct(
        private array $paths,
        private ?string $sshUser = null,
        private ?string $host = null,
        private int $port = 22
    ) {
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

    public function __toString(): string
    {
        $remotePart = "{$this->connectionString()}:";

        return collect($this->paths)
            ->map(fn (string $path) => rtrim($path, '/'))
            ->map(fn (string $path) => $remotePart.$path)
            ->implode(' ');
    }
}
