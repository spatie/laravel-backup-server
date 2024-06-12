<?php

namespace Spatie\BackupServer\Exceptions;

use Exception;
use Spatie\BackupServer\Models\Backup;

class BackupFailed extends Exception
{
    public static function sourceNotReachable(Backup $backup, string $response): self
    {
        return new static("{$backup->sourceLocation()->connectionString()} could not be reached. Response: {$response}");
    }

    public static function destinationNotReachable(Backup $backup): self
    {
        return new static("The destination disk `{$backup->destination->disk_name}` could not be reached.");
    }

    public static function rsyncDidFail(Backup $backup, string $commandOutput): static
    {
        return new static("rsync failed. Output: {$commandOutput}");
    }

    public static function BackupCommandsFailed(Backup $backup, string $attribute, string $commandOutput): static
    {
        return new static("Backup commands in attribute `{$attribute}` failed. Output: {$commandOutput}");
    }
}
