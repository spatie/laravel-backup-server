<?php

namespace Spatie\BackupServer\Commands;

use Illuminate\Console\Command;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\PerformCleanupBackupsForSourceJob;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\PerformCleanupDestinationJob;

class DispatchPerformCleanupJobsCommand extends Command
{
    protected $signature = 'backup:clean';

    protected $description = 'Dispatch cleanup jobs';

    public function handle()
    {
        $this->info('Dispatching cleanup jobs...');

        Source::each(function (Source $source) {
            $this->comment("Dispatching cleanup job for source id {$source->id}...");

            dispatch(new PerformCleanupBackupsForSourceJob($source));
        });

        Destination::each(function (Destination $destination) {
            $this->comment("Dispatching cleanup job for destination id {$destination->id}...");

            dispatch(new PerformCleanupDestinationJob($destination));
        });

        $this->info('All done!');
    }
}
