<?php

namespace Spatie\BackupServer\Models;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\BackupServer\Models\Concerns\HasAsyncDelete;
use Spatie\BackupServer\Models\Concerns\LogsActivity;
use Spatie\BackupServer\Support\Helpers\DestinationLocation;
use Spatie\BackupServer\Support\Helpers\Enums\Task;
use Spatie\BackupServer\Support\Helpers\SourceLocation;
use Spatie\BackupServer\Tasks\Backup\Support\BackupCollection;
use Spatie\BackupServer\Tasks\Backup\Support\FileList\FileList;
use Spatie\BackupServer\Tasks\Backup\Support\Rsync\RsyncProgressOutput;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\DeleteBackupJob;
use Spatie\BackupServer\Tasks\Search\ContentSearchResultFactory;
use Spatie\BackupServer\Tasks\Search\FileSearchResultFactory;
use Spatie\BackupServer\Tests\Database\Factories\BackupFactory;
use Symfony\Component\Process\Process;

class Backup extends Model
{
    use LogsActivity;
    use HasAsyncDelete;
    use HasFactory;

    public $table = 'backup_server_backups';

    public $guarded = [];

    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_DELETING = 'deleting';

    protected $casts = [
        'log' => 'array',
        'size_in_kb' => 'int',
        'real_size_in_kb' => 'int',
        'completed_at' => 'datetime',
        'pre_backup_commands' => 'array',
        'post_backup_commands' => 'array',
        'includes' => 'array',
        'excludes' => 'array',
    ];

    public static function booted()
    {
        static::deleting(function (Backup $backup) {
            if (! empty($backup->path) && $backup->disk()->exists($backup->path)) {
                $backup->disk()->deleteDirectory($backup->path);
            }
        });
    }

    protected static function newFactory(): BackupFactory
    {
        return BackupFactory::new();
    }

    public function getDeletionJobClassName(): string
    {
        return DeleteBackupJob::class;
    }

    public function newCollection(array $models = [])
    {
        return new BackupCollection($models);
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(Source::class);
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function logItems(): HasMany
    {
        return $this->hasMany(BackupLogItem::class);
    }

    public function sourceLocation(): SourceLocation
    {
        return new SourceLocation(
            $this->source->includes ?? [],
            $this->source->ssh_user,
            $this->source->host,
            $this->source->ssh_port,
        );
    }

    public function destinationLocation(): DestinationLocation
    {
        return new DestinationLocation($this->disk_name, $this->path);
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
            'completed_at' => now(),
        ]);

        return $this;
    }

    public function markAsFailed(string $errorMessage): self
    {
        $this->logError(Task::BACKUP, "Backup failed: {$errorMessage}");

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

        $rsyncOutput = new RsyncProgressOutput($progressOutput);

        if ($rsyncOutput->concernsProgress()) {
            $this->update([
                'rsync_current_transfer_speed' => $rsyncOutput->getTransferSpeed(),
            ]);
        }

        return $this;
    }

    protected function addMessageToLog(string $task, string $level, string $message): Backup
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

    public function recalculateBackupSize(): Backup
    {
        $process = Process::fromShellCommandline(
            "du -kd 0",
            $this->destinationLocation()->getFullPath(),
            null,
            null,
            config('backup-server.backup_size_calculation_timeout_in_seconds'),
        );

        $process->run();

        $output = $process->getOutput();

        $sizeInKb = Str::before($output, ' ');

        $this->update(['size_in_kb' => (int)trim($sizeInKb)]);

        return $this;
    }

    public function recalculateRealBackupSize(): Backup
    {
        if (! $this->disk()->exists($this->path)) {
            $this->update(['real_size_in_kb' => 0]);

            return $this;
        }

        $command = 'du -kd 1 ..';

        $process = Process::fromShellCommandline(
            $command,
            $this->destinationLocation()->getFullPath(),
            null,
            null,
            config('backup-server.backup_size_calculation_timeout_in_seconds'),
        );
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
        return Storage::disk($this->disk_name);
    }

    public function pathPrefix(): string
    {
        return config("filesystems.disks.{$this->disk_name}.root", '');
    }

    public function has(string $path): bool
    {
        return $this->disk()->exists("{$this->path}/{$path}");
    }

    public function findFile(string $searchFor, callable $handleSearchResult): bool
    {
        $path = $this->destinationLocation()->getFullPath();

        if (! file_exists($path)) {
            return false;
        }

        $process = Process::fromShellCommandline("find . -name \"{$searchFor}\" -print", $path);

        $process->run(function ($type, $buffer) use ($handleSearchResult) {
            if ($type === Process::ERR) {
                return null;
            }

            $fileSearchResults = FileSearchResultFactory::create($buffer, $this);

            return $handleSearchResult($fileSearchResults);
        });

        return $process->isSuccessful();
    }

    public function findContent(string $searchFor, callable $handleSearchResult): bool
    {
        $path = $this->destinationLocation()->getFullPath();

        $process = Process::fromShellCommandline("grep -onIir '{$searchFor} '  .", $path);

        $process->run(function ($type, $buffer) use ($handleSearchResult) {
            if ($type === Process::ERR) {
                return null;
            }

            $fileSearchResults = ContentSearchResultFactory::create($buffer, $this);

            return $handleSearchResult($fileSearchResults);
        });

        return $process->isSuccessful();
    }

    public function isPendingOrInProgress(): bool
    {
        return in_array($this->status, [
            Backup::STATUS_PENDING,
            Backup::STATUS_IN_PROGRESS,
        ], true);
    }

    public function isCompleted(): bool
    {
        return $this->status === static::STATUS_COMPLETED;
    }

    public function fileList(string $relativeDirectory = '/'): FileList
    {
        return new FileList($this, $relativeDirectory);
    }

    public function name(): string
    {
        return $this->source->name . '-' . optional($this->completed_at)->format('Y-m-d-His');
    }
}
