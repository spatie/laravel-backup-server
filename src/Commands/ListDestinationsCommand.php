<?php

namespace Spatie\BackupServer\Commands;

use Illuminate\Console\Command;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Support\Helpers\Format;

class ListDestinationsCommand extends Command
{
    protected $signature = 'backup-server:list-destinations';

    protected $description = 'Display a list of all destinations';

    public function handle()
    {
        $headers = ['Destination', 'Healthy', 'Total Backup Size', 'Used Storage', 'Inode Usage', 'Free Space'];

        $rows = Destination::get()
            ->map(fn (Destination $destination) => $this->convertToRow($destination));

        $this->table($headers, $rows);
    }

    protected function convertToRow(Destination $destination)
    {
        $backups = $destination->backups;

        return [
            'name' => $destination->name,
            'healthy' => Format::emoji($destination->isHealthy()),
            'total_backup_size' => Format::humanReadableSize($backups->sizeInKb()),
            'used_storage' => Format::humanReadableSize($destination->backups->realSizeInKb()),
            'inode_usage' => 'TODO',
            'free_space' => 'TODO',
        ];
    }
}
