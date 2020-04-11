<?php

namespace Spatie\BackupServer\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\BackupServer\Models\Concerns\HasAsyncDelete;
use Spatie\BackupServer\Models\Concerns\HasBackupRelation;
use Spatie\BackupServer\Models\Concerns\LogsActivity;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\DeleteSourceJob;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckCollection;
use Spatie\Ssh\Ssh;
use Symfony\Component\Process\Process;

class Source extends Model
{
    use LogsActivity, HasBackupRelation, HasAsyncDelete;

    public $table = 'backup_server_sources';

    public $guarded = [];

    const STATUS_ACTIVE = 'active';
    const STATUS_DELETING = 'deleting';

    public $casts = [
        'includes' => 'array',
        'excludes' => 'array',
        'pre_backup_commands' => 'array',
        'post_backup_commands' => 'array',
        'backup_hour' => 'integer',
    ];

    public static function booted()
    {
        static::creating(function (Source $source) {
            $source->status = static::STATUS_ACTIVE;
        });
    }

    public function getDeletionJobClassName(): string
    {
        return DeleteSourceJob::class;
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function scopeNamed(Builder $builder, string $name): void
    {
        $builder->where('name', $name)->first();
    }

    public function executeSshCommands(array $commands): Process
    {
        $ssh = new Ssh($this->ssh_user, $this->host);

        if ($this->ssh_port) {
            $ssh->usePort($this->ssh_port);
        }

        if ($this->ssh_private_key_file) {
            $ssh->usePrivateKey($this->ssh_private_key_file);
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
