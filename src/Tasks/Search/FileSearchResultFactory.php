<?php

namespace Spatie\BackupServer\Tasks\Search;

use Illuminate\Support\Collection;
use Spatie\BackupServer\Models\Backup;

class FileSearchResultFactory
{
    public static function create(string $findOutput, Backup $backup): Collection
    {
        return collect(explode(PHP_EOL, $findOutput))
            ->filter()
            ->values()
            ->map(fn (string $relativePath) => new FileSearchResult($relativePath, $backup));
    }
}
