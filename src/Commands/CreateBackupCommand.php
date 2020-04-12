<?php

namespace Spatie\BackupServer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Event;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\BackupLogItem;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Support\Helpers\Enums\LogLevel;
use Spatie\BackupServer\Tasks\Backup\Actions\CreateBackupAction;

class CreateBackupCommand extends Command
{
    protected $signature = 'backup-server:backup {sourceName}';

    protected $description = 'Create a backup';

    public function handle()
    {
        $sourceName = $this->argument('sourceName');

        $source = Source::firstWhere('name', $sourceName);

        if (! $source) {
            $this->error("There is no source named `{$sourceName}`");

            return -1;
        }

        $this->info("Creating new backup for {$sourceName}");

        $writeLogItemsToConsole = function (Backup $backup) {
            Event::listen('eloquent.saving: ' . BackupLogItem::class, function (BackupLogItem $backupLogItem) use ($backup) {
                if ($backupLogItem->backup_id !== $backup->id) {
                    return;
                }

                $outputMethod = $backupLogItem->level === LogLevel::ERROR
                    ? 'error'
                    : 'comment';

                $this->$outputMethod($backupLogItem->message);
            });
        };

        (new CreateBackupAction())
            ->doNotUseQueue()
            ->afterBackupModelCreated($writeLogItemsToConsole)
            ->execute($source, $writeLogItemsToConsole);

        return 0;
    }
}
