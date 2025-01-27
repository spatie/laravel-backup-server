<?php

namespace Spatie\BackupServer\Commands;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Search\ContentSearchResult;

class FindContentCommand extends Command
{
    protected $signature = 'backup-server:find-content {sourceName} {searchFor}';

    protected $description = 'Find content in the backups of a source';

    protected int $resultCounter = 0;

    public function handle(): ?int
    {
        $sourceName = $this->argument('sourceName');

        $searchFor = $this->argument('searchFor');

        if (! $source = Source::named($this->argument('sourceName'))->first()) {
            $this->error("Did not find a source named {$sourceName}");

            return -1;
        }

        $source->completedBackups
            ->each(function (Backup $backup) use ($searchFor) {
                $backup->findContent($searchFor, Closure::fromCallable($this->handleFoundContent(...)));
            });

        $this->comment('');
        $this->comment($this->resultCounter.' '.Str::plural('search result', $this->resultCounter).' found.');

        return null;
    }

    protected function handleFoundContent(Collection $contentSearchResults)
    {
        $contentSearchResults->each(function (ContentSearchResult $contentSearchResult) {
            $this->resultCounter++;

            $this->info($contentSearchResult->getAbsolutePath().':'.$contentSearchResult->lineNumber());
        });
    }
}
