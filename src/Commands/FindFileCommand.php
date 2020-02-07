<?php

namespace Spatie\BackupServer\Commands;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Search\FileSearchResult;
use Symfony\Component\Console\Helper\Table;

class FindFileCommand extends Command
{
    protected $signature = 'backup-server:find-file {sourceName} {searchFor}';

    protected $description = 'Find files in the backups of a source';

    protected ?Table $table;

    public function handle()
    {
        $sourceName = $this->argument('sourceName');
        $searchFor = $this->argument('searchFor');

        $this->info("Searching all backups of `{$sourceName}` for files named `{$searchFor}`...");

        if (!$source = Source::named($this->argument('sourceName'))->first()) {
            $this->info("Did not find a source named {$sourceName}");

            return true;
        }

        $section = $this->output->output->section();
        $this->table = new Table($section);

        $this->table->setHeaders(['File', 'Age']);
        $this->table->render();

        $source->completedBackups
            ->each(function (Backup $backup) use ($searchFor) {
                $backup->findFile($searchFor, Closure::fromCallable([$this, 'handleFoundFile']));
            });

        $this->info('');
        $this->info('All done!');
    }


    protected function handleFoundFile(Collection $fileSearchResults)
    {
        $fileSearchResults->each(function (FileSearchResult $fileSearchResult) {
            $this->table->appendRow([$fileSearchResult->getAbsolutePath(), $fileSearchResult->backup->created_at->diffForHumans()]);

            //$this->info("{$fileSearchResult->path()} {$fileSearchResult->backup->created_at->diffForHumans()}");
        });
    }
}
