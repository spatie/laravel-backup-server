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
        $headers = ['Destination', 'Healthy', 'Total Backup Size', 'Used Storage', 'Free Space', 'Capacity Used', 'Inode Usage'];

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
            'free_space' => Format::humanReadableSize($destination->getFreeSpaceInKb()),
            'capacity_used' => $destination->getUsedSpaceInPercentage() . '%',
            'inode_usage' => $destination->getInodeUsagePercentage() . '%',
        ];
    }
}
