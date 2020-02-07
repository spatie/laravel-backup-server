<?php

namespace Spatie\BackupServer\Commands;

use Closure;
use Illuminate\Console\Command;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Source;

class FindFileCommand extends Command
{
    protected $signature = 'backup-server:find-file {sourceName} {searchFor}';

    protected $description = 'Find files in the backups of a source';

    public function handle()
    {
        $sourceName = $this->argument('sourceName');
        $searchFor = $this->argument('searchFor');

        $this->info("Searching all backups of `{$sourceName}` for files named `{$searchFor}`...");

        if (!$source = Source::named($this->argument('sourceName'))->first()) {
            $this->info("Did not find a source named {$sourceName}");

            return true;
        }

        $source->completedBackups
            ->each(function (Backup $backup) use ($searchFor) {
                $backup->findFile($searchFor, Closure::fromCallable([$this, 'handleFoundFile']));
            });

        $this->info('All done!');
    }


    protected function handleFoundFile(Collection $fileSearchResults)
    {
        $fileSearchResults->each(function (FileSearchResult $fileSearchResult) {
            $this->info("{$fileSearchResult->path()} {$fileSearchResult->backup->created_at->diffForHumans()}");
        });
    }
}
