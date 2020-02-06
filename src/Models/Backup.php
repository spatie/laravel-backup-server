<?php

namespace Spatie\BackupServer\Models;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\BackupServer\Models\Concerns\LogsActivity;
use Spatie\BackupServer\Support\Helpers\DestinationLocation;
use Spatie\BackupServer\Support\Helpers\Enums\Task;
use Spatie\BackupServer\Support\Helpers\SourceLocation;
use Spatie\BackupServer\Tasks\Backup\Support\BackupCollection;
use Spatie\BackupServer\Tasks\Backup\Support\RsyncOutput;
use Symfony\Component\Process\Process;

class Backup extends Model
{
    use LogsActivity;

    public $guarded = [];

    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';

    protected $casts = [
        'log' => 'array',
        'size_in_kb' => 'int',
        'real_size_in_kb' => 'int',
    ];

    public static function boot()
    {
        parent::boot();

        static::deleting(function (Backup $backup) {
            $backup->disk()->deleteDirectory($backup->path);
        });
    }

    public function newCollection(array $models = [])
    {
        return new BackupCollection($models);
    }

    public function source()
    {
        return $this->belongsTo(Source::class);
    }

    public function destination()
    {
        return $this->belongsTo(Destination::class);
    }

    public function logItems(): HasMany
    {
        return $this->hasMany(BackupLogItem::class);
    }

    public function sourceLocation()
    {
        return new SourceLocation(
            $this->source->includes,
            $this->source->ssh_user,
            $this->source->host,
            $this->source->ssh_port,
        );
    }

    public function destinationLocation(): DestinationLocation
    {
        return new DestinationLocation($this->disk(), $this->path);
    }

    public function markAsInProgress(): self
    {
        $this->update([
            'status' => self::STATUS_IN_PROGRESS,
        ]);

        return $this;
    }

    public function markAsCompleted(): self
    {
        $this->logInfo(Task::BACKUP, 'Backup completed.');

        $this->update([
            'status' => self::STATUS_COMPLETED,
        ]);

        return $this;
    }

    public function markAsFailed(string $errorMessage): self
    {
        $this->logError(Task::BACKUP, "Backup failed: `{$errorMessage}`");

        $this->update([
            'status' => self::STATUS_FAILED,
        ]);

        return $this;
    }

    public function scopeCompleted(Builder $query): void
    {
        $query->where('status', static::STATUS_COMPLETED);
    }

    public function scopeFailed(Builder $query): void
    {
        $query->where('status', static::STATUS_FAILED);
    }

    public function handleProgress(string $type, string $progressOutput): self
    {
        if ($type === Process::ERR) {
            $this->logError(Task::BACKUP, $progressOutput);

            return $this;
        }

        $rsyncOutput = new RsyncOutput($progressOutput);

        if ($rsyncOutput->concernsProgress()) {
            $this->update([
                'transfer_speed' => $rsyncOutput->getTransferSpeed()
            ]);

            return $this;
        }

        if ($rsyncOutput->isSummpary()) {
            $this->logInfo(Task::BACKUP, $progressOutput);

            return $this;
        }

        return $this;
    }

    protected function addMessageToLog(string $task, string $level, string $message)
    {
        $this->logItems()->create([
           'source_id' => $this->source_id,
           'destination_id' => $this->destination_id,
           'task' => $task,
           'level' => $level,
           'message' => trim($message),
        ]);

        return $this;
    }

    public function recalculateBackupSize()
    {
        $process = Process::fromShellCommandline("du -kd 0", $this->destinationLocation()->getFullPath());

        $process->run();

        $output = $process->getOutput();

        $sizeInKb = Str::before($output, ' ');

        $this->update(['size_in_kb' => (int)trim($sizeInKb)]);

        return $this;
    }

    public function recalculateRealBackupSize()
    {
        if (! $this->disk()->exists($this->path)) {
            $this->update(['real_size_in_kb' => 0]);

            return $this;
        }

        $command = 'du -kd 1 ..';

        $process = Process::fromShellCommandline($command, $this->destinationLocation()->getFullPath());
        $process->run();

        $output = $process->getOutput();

        $directoryLine = collect(explode(PHP_EOL, $output))->first(function (string $line) {
            return Str::contains($line, $this->destinationLocation()->getDirectory());
        });

        $sizeInKb = Str::before($directoryLine, "\t");

        $this->update(['real_size_in_kb' => (int)trim($sizeInKb)]);

        return $this;
    }

    public function existsOnDisk(): bool
    {
        return $this->destination->disk()->exists($this->path);
    }

    public function disk(): Filesystem
    {
        return Storage::disk($this->disk);
    }

    public function has(string $path): bool
    {
        return $this->disk()->exists("{$this->path}/{$path}");
    }
}
