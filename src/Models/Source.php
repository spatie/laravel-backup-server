<?php

namespace Spatie\BackupServer\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\BackupServer\Models\Concerns\HasAsyncDelete;
use Spatie\BackupServer\Models\Concerns\HasBackupRelation;
use Spatie\BackupServer\Models\Concerns\LogsActivity;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\DeleteSourceJob;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckCollection;
use Spatie\BackupServer\Tests\Database\Factories\SourceFactory;
use Spatie\Ssh\Ssh;
use Symfony\Component\Process\Process;

class Source extends Model
{
    use LogsActivity;
    use HasBackupRelation;
    use HasAsyncDelete;
    use HasFactory;

    public $table = 'backup_server_sources';

    public $guarded = [];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_DELETING = 'deleting';

    public $casts = [
        'healthy' => 'boolean',
        'includes' => 'array',
        'excludes' => 'array',
        'pre_backup_commands' => 'array',
        'post_backup_commands' => 'array',
    ];

    public static function booted()
    {
        static::creating(function (Source $source) {
            $source->status = static::STATUS_ACTIVE;
        });
    }

    protected static function newFactory(): SourceFactory
    {
        return SourceFactory::new();
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
        $healthCheckClassNames = config('backup-server.monitor.source_health_checks');

        return new HealthCheckCollection($healthCheckClassNames, $this);
    }

    public function scopeHealthy(Builder $query): void
    {
        $query->where('healthy', true);
    }

    public function scopeUnhealthy(Builder $query): void
    {
        $query->where('healthy', false);
    }

    protected function addMessageToLog(string $task, string $level, string $message): Source
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
