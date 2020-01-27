<?php

namespace Spatie\BackupServer\Commands;

use Illuminate\Console\Command;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Backup\Jobs\PerformBackupJob;

class DispatchPerformBackupJobsCommand extends Command
{
    protected $signature = 'backup:run';

    protected $description = 'Dispatch backup jobs';

    public function handle()
    {
        $this->info('Dispatching backup jobs...');

        Source::each(function (Source $source) {
            $this->comment("Dispatching backup job for source id `{$source->id}`");

            /** @var \Spatie\BackupServer\Models\Backup $backup */
            $backup = Backup::create([
                'status' => Backup::STATUS_PENDING,
                'source_id' => $source->id,
                'destination_id' => $source->destination->id,
                'disk' => $source->destination->disk,
            ]);

            dispatch(new PerformBackupJob($backup));
        });

        $this->info('All done!');
    }
}
