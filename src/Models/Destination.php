<?php

namespace Spatie\BackupServer\Models;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\BackupServer\Models\Concerns\HasBackupRelation;
use Spatie\BackupServer\Models\Concerns\LogsActivity;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckCollection;
use Symfony\Component\Process\Process;

class Destination extends Model
{
    public $guarded = [];

    use LogsActivity, HasBackupRelation;

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
            $healthCheckClassNames = config('backup-server.monitor.destination_health_checks');

            $healthCheckCollection = new HealthCheckCollection($healthCheckClassNames, $this);
        }

        return $healthCheckCollection;
    }

    public function getInodeUsagePercentage(): int
    {
        $diskRootPath = Storage::disk('backups')->path('');

        $command =  PHP_OS === 'Darwin'
            ? 'df "$PWD" | awk \'{print $8}\''
            : 'df --output=ipcent "$PWD"';

        $process = Process::fromShellCommandline("cd {$diskRootPath}; {$command}");
        $process->run();

        if (! $process->isSuccessful()) {
            throw new Exception("Could not determine inode count");
        }

        $lines  = explode(PHP_EOL, $process->getOutput());

        $percentageString = $lines[1];

        return (int)Str::before('%', $percentageString);
    }
}
