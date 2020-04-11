<?php

namespace Spatie\BackupServer\Commands;

use Illuminate\Console\Command;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Backup\Actions\CreateBackupAction;
use Spatie\BackupServer\Tasks\Backup\Support\BackupScheduler\BackupScheduler;

class DispatchPerformBackupJobsCommand extends Command
{
    protected $signature = 'backup-server:backup';

    protected $description = 'Dispatch backup jobs';

    public function handle()
    {
        $this->info('Dispatching backup jobs...');

        $backupScheduler = app(BackupScheduler::class);

        Source::cursor()
            ->filter(fn (Source $source) => $backupScheduler->shouldBackupNow($source))
            ->each(function (Source $source) {
                $this->comment("Dispatching backup job for source `{$source->name}` (id: {$source->id})");

                (new CreateBackupAction())->execute($source);
            });

        $this->info('All done!');
    }
}
