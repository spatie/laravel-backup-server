<?php

namespace Spatie\BackupServer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\BackupServer\Models\Concerns\LogsActivity;
use Spatie\BackupServer\Support\Ssh;
use Symfony\Component\Process\Process;

class Source extends Model
{
    use LogsActivity;

    public $guarded = [];

    public $casts = [
        'includes' => 'array',
        'excludes' => 'array',
        'pre_backup_commands' => 'array',
        'post_backup_commands' => 'array',
    ];

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function backups(): HasMany
    {
        return $this->hasMany(Backup::class)->orderByDesc('created_at');
    }

    public function completedBackups(): HasMany
    {
        return $this->backups()->completed();
    }

    public function executeSshCommands(array $commands): Process
    {
        $ssh = new Ssh($this->ssh_user, $this->host);

        return $ssh->execute($commands);
    }

    protected function addMessageToLog(string $task, string $level, string $message)
    {
        $this->logItems()->create([
            'destination_id' => $this->destination_id,
            'task' => $task,
            'level' => $level,
            'message' => trim($message),
        ]);

        return $this;
    }
}
