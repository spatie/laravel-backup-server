<?php

namespace Spatie\BackupServer\Tasks\Search;

use Spatie\BackupServer\Models\Backup;

class FileSearchResult
{
    private string $relativePath;

    private Backup $backup;

    public function __construct(string $relativePath, Backup $backup)
    {
        $this->relativePath = $relativePath;

        $this->backup = $backup;
    }
}
