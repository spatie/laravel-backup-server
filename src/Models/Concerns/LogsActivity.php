<?php

namespace Spatie\BackupServer\Models\Concerns;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\BackupServer\Models\BackupLogItem;
use Spatie\BackupServer\Support\Helpers\Enums\LogLevel;
use Spatie\BackupServer\Support\Helpers\Enums\Task;

trait LogsActivity
{
    public function logItems(): HasMany
    {
        return $this->hasMany(BackupLogItem::class);
    }

    public function logInfo(Task $task, string $text): void
    {
        $this->addMessageToLog($task, LogLevel::INFO, $text);
    }

    public function logError(Task $task, string $text): void
    {
        $this->addMessageToLog($task, LogLevel::ERROR, $text);
    }

    abstract protected function addMessageToLog(Task $task, string $level, string $message);
}
