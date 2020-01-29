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

    public function reachable(): bool
    {
        try {
            $this->disk();

            return true;
        } catch (Exception $exception) {
            return false;
        }
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
        $healthCheckClassNames = config('backup-server.monitor.destination_health_checks');

        $healthCheckCollection = new HealthCheckCollection($healthCheckClassNames, $this);

        return $healthCheckCollection;
    }

    public function getInodeUsagePercentage(): int
    {
        $rawOutput = $this->getDfOutput(8, 'ipcent');

        return (int)Str::before($rawOutput, '%');
    }

    public function getFreeSpaceInKb(): int
    {
        $rawOutput = $this->getDfOutput(4, 'avail');

        return (int)$rawOutput;
    }

    public function getUsedSpaceInPercentage(): int
    {
        $rawOutput = $this->getDfOutput(5, 'pcent');

        return (int)Str::before($rawOutput, '%');
    }

    protected function getDfOutput(int $macOsColumnNumber, $linuxOutputFormat)
    {
        $command = PHP_OS === 'Darwin'
            ? 'df -k "$PWD" | awk \'{print $' . $macOsColumnNumber . '}\''
            : 'df -k --output=' . $linuxOutputFormat . ' "$PWD"';

        $diskRootPath = $this->disk()->path('');

        $process = Process::fromShellCommandline("cd {$diskRootPath}; {$command}");
        $process->run();

        if (!$process->isSuccessful()) {
            throw new Exception("Could not determine inode count");
        }

        $lines = explode(PHP_EOL, $process->getOutput());

        return $lines[1];
    }
}
