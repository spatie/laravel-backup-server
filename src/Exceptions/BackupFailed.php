<?php

namespace Spatie\BackupServer\Exceptions;

use Exception;
use Spatie\BackupServer\Models\Backup;

class BackupFailed extends Exception
{
    public static function sourceNotReachable(Backup $backup, string $response): self
    {
        return new static("Backup for `{$backup->source->name}` failed because {$backup->sourceLocation()->connectionString()} could not be reached. Response: {$response}");
    }

    public static function BackupCommandsFailed(Backup $backup, string $attribute, string $commandOutput)
    {
        return new static("Backup for `{$backup->source->name}` failed because the backup commands in attribute `{$attribute}` failed. Output: {$commandOutput}");
    }
}
