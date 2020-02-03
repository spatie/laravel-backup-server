<?php

namespace Spatie\BackupServer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\BackupServer\Models\Concerns\HasBackupRelation;
use Spatie\BackupServer\Models\Concerns\LogsActivity;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckCollection;
use Spatie\Ssh\Ssh;
use Symfony\Component\Process\Process;

class Source extends Model
{
    use LogsActivity, HasBackupRelation;

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

    public function executeSshCommands(array $commands): Process
    {
        $ssh = new Ssh($this->ssh_user, $this->host);

        if ($this->ssh_port) {
            $ssh->port($this->ssh_port);
        }

        return $ssh->execute($commands);
    }

    public function isHealthy(): bool
    {
        return $this->getHealthChecks()->allPass();
    }

    public function getHealthChecks(): HealthCheckCollection
    {
        static $healthCheckCollection = null;

        if (is_null($healthCheckCollection)) {
            $healthCheckClassNames = config('backup-server.monitor.source_health_checks');

            $healthCheckCollection = new HealthCheckCollection($healthCheckClassNames, $this);
        }

        return $healthCheckCollection;
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
