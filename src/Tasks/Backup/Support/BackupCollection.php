<?php

namespace Spatie\BackupServer\Tasks\Backup\Support;

use Illuminate\Database\Eloquent\Collection;
use Spatie\BackupServer\Models\Backup;

class BackupCollection extends Collection
{
    public function realSizeInKb(): int
    {
        return $this->sum(function (Backup $backup) {
            return $backup->real_size_in_kb;
        });
    }

    public function sizeInKb(): int
    {
        return $this->sum(function (Backup $backup) {
            return $backup->size_in_kb;
        });
    }

    public function youngest(): ?Backup
    {
        return $this->first();
    }

    public function oldest(): ?Backup
    {
        return $this->reverse()->last();
    }
}
