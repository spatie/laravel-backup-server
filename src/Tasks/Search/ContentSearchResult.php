<?php

namespace Spatie\BackupServer\Tasks\Search;

use Illuminate\Support\Str;
use Spatie\BackupServer\Models\Backup;

class ContentSearchResult
{
    private string $relativePath;

    private int $lineNumber;

    public function __construct(string $grepResultLine, private Backup $backup)
    {
        [$this->relativePath, $this->lineNumber] = explode(':', $grepResultLine);
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
