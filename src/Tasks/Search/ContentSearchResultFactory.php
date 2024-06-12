<?php

namespace Spatie\BackupServer\Tasks\Search;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\BackupServer\Models\Backup;

class ContentSearchResultFactory
{
    public static function create(string $processOutput, Backup $backup): Collection
    {
        return collect(explode(PHP_EOL, $processOutput))
            ->filter()
            ->filter(function (string $outputLine): bool {
                if (! Str::startsWith($outputLine, '.')) {
                    return false;
                }

                if (! Str::contains($outputLine, ':')) {
                    return false;
                }

                return true;
            })
            ->values()
            ->map(fn (string $relativePath): \Spatie\BackupServer\Tasks\Search\ContentSearchResult => new ContentSearchResult($relativePath, $backup));
    }
}
