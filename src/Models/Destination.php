<?php

namespace Spatie\BackupServer\Models;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\BackupServer\Models\Concerns\LogsActivity;

class Destination extends Model
{
    public $guarded = [];

    use LogsActivity;

    public function backups()
    {
        $this->hasMany(Backup::class);
    }

    public function disk(): Filesystem
    {
        return Storage::disk($this->disk);
    }

    protected function addMessageToLog(string $task, string $level, string $message)
    {
        $this->logItems()->create([
            'task' => $task,
            'level' => $level,
            'message' => trim($message),
        ]);
    }
}
