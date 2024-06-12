<?php

namespace Spatie\BackupServer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Spatie\BackupServer\Exceptions\InvalidCommandInput;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Support\AlignCenterTableStyle;
use Spatie\BackupServer\Support\AlignRightTableStyle;
use Spatie\BackupServer\Support\Helpers\Format;

class ListSourcesCommand extends Command
{
    protected $signature = 'backup-server:list
                            {--sortBy= : choose between name, id, healthy, backup_count, newest_backup, youngest_backup_size, backup_size and used_storage}
                            {--D|desc}';

    protected $description = 'Display a list of the last backup of all sources';

    protected array $headers = [
        'name' => 'Source',
        'id' => 'Id',
        'healthy' => 'Healthy',
        'backup_count' => '# of Backups',
        'newest_backup' => 'Youngest Backup Age',
        'youngest_backup_size' => 'Youngest Backup Size',
        'backup_size' => 'Total Backup Size',
        'used_storage' => 'Used storage',
    ];

    public function handle()
    {
        $sortBy = (string) ($this->option('sortBy') ?? 'name');

        $this->guardAgainstInvalidOptionValues($sortBy);

        $rows = Source::all()
            ->map(fn (Source $source) => $this->convertToRow($source))
            ->sortBy($sortBy, SORT_REGULAR, $this->option('desc'))
            ->map(fn (Collection $data) => $this->makeRowReadable($data));

        $headers = array_values($this->headers);

        $columnStyles = collect($headers)
            ->map(function (string $header) {
                if (in_array($header, ['Id', 'Youngest Backup Size', '# of Backups', 'Total Backup Size', 'Used storage'])) {
                    return new AlignRightTableStyle();
                }

                if ($header === 'Healthy') {
                    return new AlignCenterTableStyle();
                }

                return null;
            })
            ->filter()
            ->all();

        $this->table($headers, $rows, 'default', $columnStyles);
    }

    protected function makeRowReadable(Collection $row): array
    {
        return [
            'name' => $row->get('name'),
            'id' => $row->get('id'),
            'healthy' => Format::emoji($row->get('healthy')),
            'backup_count' => $row->get('backup_count'),
            'newest_backup' => $row->get('newest_backup') ? Format::ageInDays($row->get('newest_backup')) : 'No backups present',
            'youngest_backup_size' => $row->get('youngest_backup_size') ? Format::KbToHumanReadableSize($row->get('youngest_backup_size')) : '/',
            'backup_size' => $row->get('backup_size') ? Format::KbToHumanReadableSize($row->get('backup_size')) : '/',
            'used_storage' => $row->get('used_storage') ? Format::KbToHumanReadableSize($row->get('used_storage')) : '/',
        ];
    }

    protected function convertToRow(Source $source): Collection
    {
        $completedBackups = $source->completedBackups;

        $youngestBackup = $completedBackups->youngest();

        return collect([
            'name' => $source->name,
            'id' => $source->id,
            'healthy' => $source->isHealthy(),
            'backup_count' => $completedBackups->count(),
            'newest_backup' => $youngestBackup->created_at ?? null,
            'youngest_backup_size' => $youngestBackup->size_in_kb ?? null,
            'backup_size' => $completedBackups->sizeInKb(),
            'used_storage' => $completedBackups->realSizeInKb(),
        ]);
    }

    protected function getFormattedBackupDate(?Backup $backup = null)
    {
        return is_null($backup)
            ? 'No backups present'
            : Format::ageInDays($backup->created_at);
    }

    public function guardAgainstInvalidOptionValues(string $optionValue): void
    {
        if (! array_key_exists($optionValue, $this->headers)) {
            throw InvalidCommandInput::byOption($optionValue, array_keys($this->headers));
        }
    }
}
