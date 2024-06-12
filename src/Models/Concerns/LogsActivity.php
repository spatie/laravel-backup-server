<?php

namespace Spatie\BackupServer\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\BackupServer\Models\BackupLogItem;
use Spatie\BackupServer\Support\Helpers\Enums\LogLevel;

trait LogsActivity
{
    public function logItems(): HasMany
    {
        return $this->hasMany(BackupLogItem::class);
    }

    public function logInfo(string $task, string $text): void
    {
        $this->addMessageToLog($task, LogLevel::INFO, $text);
    }

    public function logError(string $task, string $text): void
    {
        $this->addMessageToLog($task, LogLevel::ERROR, $text);
    }

    abstract protected function addMessageToLog(string $task, string $level, string $message);
}
