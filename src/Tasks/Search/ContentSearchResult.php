<?php

namespace Spatie\BackupServer\Tasks\Search;

use Illuminate\Support\Str;
use Spatie\BackupServer\Models\Backup;

class ContentSearchResult
{
    private string $relativePath;

    private int $lineNumber;

    private Backup $backup;

    public function __construct(string $grepResultLine, Backup $backup)
    {
        [$this->relativePath, $this->lineNumber] = explode(':', $grepResultLine);

        $this->backup = $backup;
    }

    public function lineNumber(): string
    {
        return $this->lineNumber;
    }

    public function getAbsolutePath(): string
    {
        $root = $this->backup->destinationLocation()->getFullPath();

        return $root.Str::after($this->relativePath, './');
    }

    public function age(): string
    {
        return $this->backup->created_at->diffForHumans();
    }
}
