<?php

namespace Spatie\BackupServer\Commands;

use Illuminate\Console\Command;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\PerformCleanupDestinationJob;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\PerformCleanupSourceJob;

class DispatchPerformCleanupJobsCommand extends Command
{
    protected $signature = 'backup-server:cleanup';

    protected $description = 'Dispatch cleanup jobs';

    public function handle(): void
    {
        $this->info('Dispatching cleanup jobs...');

        Source::each(function (Source $source) {
            $this->comment("Dispatching cleanup job for source `{$source->name}` (id: {$source->id})...");

            dispatch(new PerformCleanupSourceJob($source));
        });

        Destination::each(function (Destination $destination) {
            $this->comment("Dispatching cleanup job for destination `{$destination->name}` (id: {$destination->id})...");

            dispatch(new PerformCleanupDestinationJob($destination));
        });

        $this->info('All done!');
    }
}
