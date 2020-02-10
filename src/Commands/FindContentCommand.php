<?php

namespace Spatie\BackupServer\Commands;

use Closure;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Search\ContentSearchResult;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class FindContentCommand extends Command
{
    protected $signature = 'backup-server:find-content {sourceName} {searchFor}';

    protected $description = 'Find content in the backups of a source';

    protected ?Table $table;

    protected OutputInterface $symfonyOutput;

    public function run(InputInterface $input, OutputInterface $output)
    {
        $this->symfonyOutput = $output;

        return parent::run($input, $output);
    }

    public function handle()
    {
        $sourceName = $this->argument('sourceName');
        $searchFor = $this->argument('searchFor');

        if (!$source = Source::named($this->argument('sourceName'))->first()) {
            $this->info("Did not find a source named {$sourceName}");

            return -1;
        }

        $this->table = new Table($this->symfonyOutput->section());

        $this->table->setHeaders(['File', 'Line', 'Age']);
        $this->table->render();


        $source->completedBackups
            ->each(function (Backup $backup) use ($searchFor) {
                $backup->findContent($searchFor, Closure::fromCallable([$this, 'handleFoundContent']));
            });

        $this->info('');
        $this->info('All done!');
    }


    protected function handleFoundContent(Collection $contentSearchResults)
    {
        $contentSearchResults->each(function (ContentSearchResult $contentSearchResult) {
            $this->table->appendRow([
                $contentSearchResult->getAbsolutePath(),
                $contentSearchResult->lineNumber(),
                $contentSearchResult->age()
            ]);
        });
    }
}
