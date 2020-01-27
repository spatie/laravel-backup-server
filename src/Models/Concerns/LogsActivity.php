<?php

namespace Spatie\BackupServer\Models\Concerns;

use Spatie\BackupServer\Models\BackupLogItem;
use Spatie\BackupServer\Support\Enums\LogLevel;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait LogsActivity
{
    public function logItems(): HasMany
    {
        return $this->hasMany(BackupLogItem::class);
    }

    public function logInfo(string $task, string $text)
    {
        $this->addMessageToLog($task, LogLevel::INFO, $text);
    }

    public function logError(string $task, string $text)
    {
        $this->addMessageToLog($task, LogLevel::ERROR, $text);
    }

    protected abstract function addMessageToLog(string $task, string $level, string $message);
}
