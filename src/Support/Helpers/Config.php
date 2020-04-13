<?php

namespace Spatie\BackupServer\Support\Helpers;

class Config
{
    public static function getQueueConnection(): ?string
    {
        return config('backup-server.queue_connection') ?? env('QUEUE_CONNECTION');
    }
}
