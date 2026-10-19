<?php

namespace Spatie\BackupServer\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\BackupServer\Models\Backup;

trait HasBackupRelation
{
    public function completedBackups(): HasMany
    {
        return $this->backups()->completed();
    }

    public function youngestCompletedBackup(): ?Backup
    {
        return $this->completedBackups()->first();
    }
}
