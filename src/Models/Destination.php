<?php

namespace Spatie\BackupServer\Models;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\BackupServer\Models\Concerns\HasBackupRelation;
use Spatie\BackupServer\Models\Concerns\LogsActivity;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckCollection;

class Destination extends Model
{
    public $guarded = [];

    use LogsActivity, HasBackupRelation;

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

    public function isHealthy(): bool
    {
        return $this->getHealthChecks()->allPass();
    }

    public function getHealthChecks(): HealthCheckCollection
    {
        static $healthCheckCollection = null;

        if (is_null($healthCheckCollection)) {
            $healthCheckClassNames = config('laravel-backup-server.monitor.destination_health_checks');

            $healthCheckCollection = new HealthCheckCollection($healthCheckClassNames, $this);
        }

        return $healthCheckCollection;
    }
}
