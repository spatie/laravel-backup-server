<?php

namespace Spatie\BackupServer\Models;

use Exception;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\BackupServer\Enums\DestinationStatus;
use Spatie\BackupServer\Models\Concerns\HasAsyncDelete;
use Spatie\BackupServer\Models\Concerns\HasBackupRelation;
use Spatie\BackupServer\Models\Concerns\LogsActivity;
use Spatie\BackupServer\Support\Helpers\Enums\LogLevel;
use Spatie\BackupServer\Support\Helpers\Enums\Task;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\DeleteDestinationJob;
use Spatie\BackupServer\Tasks\Monitor\HealthCheckCollection;
use Spatie\BackupServer\Tests\Database\Factories\DestinationFactory;
use Symfony\Component\Process\Process;

class Destination extends Model
{
    use HasAsyncDelete;
    use HasBackupRelation;
    use HasFactory;
    use LogsActivity;

    public $table = 'backup_server_destinations';

    public $guarded = [];

    public static function booted(): void
    {
        static::creating(function (Destination $source) {
            $source->status = DestinationStatus::Active;
        });
    }

    protected static function newFactory(): DestinationFactory
    {
        return DestinationFactory::new();
    }

    public function getDeletionJobClassName(): string
    {
        return DeleteDestinationJob::class;
    }

    public function disk(): Filesystem
    {
        return Storage::disk($this->disk_name);
    }

    public function reachable(): bool
    {
        try {
            $this->disk();

            return true;
        } catch (Exception) {
            return false;
        }
    }

    protected function addMessageToLog(Task $task, LogLevel $level, string $message): void
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

        return new HealthCheckCollection($healthCheckClassNames, $this);
    }

    public function getInodeUsagePercentage(): int
    {
        $rawOutput = $this->getDfOutput(8, 'ipcent');

        return (int) Str::before($rawOutput, '%');
    }

    public function getFreeSpaceInKb(): int
    {
        $rawOutput = $this->getDfOutput(4, 'avail');

        return (int) $rawOutput;
    }

    public function getUsedSpaceInPercentage(): int
    {
        $rawOutput = $this->getDfOutput(5, 'pcent');

        return (int) Str::before($rawOutput, '%');
    }

    protected function getDfOutput(int $macOsColumnNumber, string $linuxOutputFormat): string
    {
        $command = PHP_OS === 'Darwin'
            ? 'df -k "$PWD" | awk \'{print $'.$macOsColumnNumber.'}\''
            : 'df -k --output='.$linuxOutputFormat.' "$PWD"';

        $diskRootPath = $this->disk()->path('');

        $process = Process::fromShellCommandline("cd {$diskRootPath}; {$command}");
        $process->run();

        if (! $process->isSuccessful()) {
            throw new Exception('Could not determine inode count');
        }

        $lines = explode(PHP_EOL, $process->getOutput());

        return $lines[1];
    }
}
