<?php

namespace Spatie\BackupServer\Commands;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Spatie\BackupServer\Commands\Concerns\HasOutputSection;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Search\FileSearchResult;
use Symfony\Component\Console\Helper\Table;

class FindFilesCommand extends Command
{
    use HasOutputSection;

    protected $signature = 'backup-server:find-files {sourceName} {searchFor}';

    protected $description = 'Find files in the backups of a source';

    protected Table $table;

    protected int $resultCounter = 0;

    public function handle()
    {
        $sourceName = $this->argument('sourceName');

        $searchFor = $this->argument('searchFor');

        $this->info("Searching all backups of `{$sourceName}` for files named `{$searchFor}`...");

        if (! $source = Source::named($this->argument('sourceName'))->first()) {
            $this->info("Did not find a source named {$sourceName}");

            return true;
        }

        $this->table = new Table($this->getSection());

        $this->table->setHeaders(['File', 'Age']);

        $source->completedBackups
            ->each(function (Backup $backup) use ($searchFor) {
                $backup->findFile($searchFor, Closure::fromCallable([$this, 'handleFoundFile']));
            });

        $this->info('');

        $this->info(Str::plural($this->resultCounter, 'search result') . ' found.');

        return 0;
    }

    protected function handleFoundFile(Collection $fileSearchResults)
    {
        $fileSearchResults->each(function (FileSearchResult $fileSearchResult) {
            $this->resultCounter++;

            $this->table->appendRow([$fileSearchResult->getAbsolutePath(), $fileSearchResult->age()]);
        });

        $this->output;
    }
}
