<?php

namespace Spatie\BackupServer\Tasks\Search;

use Illuminate\Support\Collection;
use Spatie\BackupServer\Models\Backup;

class FileSearchResultFactory
{
    public static function create(string $processOutput, Backup $backup): Collection
    {
        return collect(explode(PHP_EOL, $processOutput))
            ->filter()
            ->values()
            ->map(fn (string $relativePath) => new FileSearchResult($relativePath, $backup));
    }
}
