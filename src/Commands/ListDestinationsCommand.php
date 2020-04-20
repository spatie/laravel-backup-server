<?php

namespace Spatie\BackupServer\Commands;

use Illuminate\Console\Command;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Support\AlignRightTableStyle;
use Spatie\BackupServer\Support\Helpers\Format;

class ListDestinationsCommand extends Command
{
    protected $signature = 'backup-server:list-destinations';

    protected $description = 'Display a list of all destinations';

    public function handle()
    {
        $headers = [
            'Destination',
            'Healthy',
            'Total Backup Size',
            'Used Storage',
            'Free Space',
            'Capacity Used',
            'Inode Usage',
        ];

        $rows = Destination::get()
            ->map(fn (Destination $destination) => $this->convertToRow($destination));

        $columnStyles = collect($headers)
            ->filter(fn (string $header) => in_array($header, [
                'Total Backup Size',
                'Used Storage',
                'Free Space',
                'Capacity Used',
                'Inode Usage',
            ]))
            ->map(fn () => new AlignRightTableStyle())
            ->all();

        $this->table($headers, $rows, 'default', $columnStyles);
    }

    protected function convertToRow(Destination $destination)
    {
        $backups = $destination->backups;

        $rowValues = [
            'name' => $destination->name,
            'healthy' => Format::emoji($destination->isHealthy()),
            'total_backup_size' => Format::KbTohumanReadableSize($backups->sizeInKb()),
            'used_storage' => Format::KbTohumanReadableSize($destination->backups->realSizeInKb()),
        ];

        if ($destination->reachable()) {
            return array_merge($rowValues, [
                'free_space' => Format::KbTohumanReadableSize($destination->getFreeSpaceInKb()),
                'capacity_used' => $destination->getUsedSpaceInPercentage() . '%',
                'inode_usage' => $destination->getInodeUsagePercentage() . '%',
            ]);
        }

        return $rowValues;
    }
}
