<?php

namespace Spatie\BackupServer\Tasks\Summary;

use Carbon\Carbon;

class ServerSummary
{
    public function __construct(
        public Carbon $from,
        public Carbon $to,
        public int $successfulBackups,
        public int $failedBackups,
        public int $healthyDestinations,
        public int $unhealthyDestinations,
        public int $healthySources,
        public int $unhealthySources,
        public int $destinationUsedSpaceInKb,
        public int $destinationFreeSpaceInKb,
        public int $timeSpentRunningBackupsInSeconds,
        public int $errorsInLog
    ) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
