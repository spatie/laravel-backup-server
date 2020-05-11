<?php

namespace Spatie\BackupServer\Tasks\Summary;

use Carbon\Carbon;

class ServerSummary
{
    public Carbon $from;

    public Carbon $to;

    public int $successfulBackups;

    public int $failedBackups;

    public int $healthyDestinations;

    public int $unhealthyDestinations;

    public int $healthySources;

    public int $unhealthySources;

    public int $destinationUsedSpaceInKb;

    public int $destinationFreeSpaceInKb;

    public int $timeSpentRunningBackupsInSeconds;

    public int $errorsInLog;

    public function __construct(
        Carbon $from,
        Carbon $to,
        int $successfulBackups,
        int $failedBackups,
        int $healthyDestinations,
        int $unhealthyDestinations,
        int $healthySources,
        int $unhealthySources,
        int $destinationUsedSpaceInKb,
        int $destinationFreeSpaceInKb,
        int $timeSpentRunningBackupsInSeconds,
        int $errorsInLog
    ) {
        $this->from = $from;
        $this->to = $to;
        $this->successfulBackups = $successfulBackups;
        $this->failedBackups = $failedBackups;
        $this->healthyDestinations = $healthyDestinations;
        $this->unhealthyDestinations = $unhealthyDestinations;
        $this->healthySources = $healthySources;
        $this->unhealthySources = $unhealthySources;
        $this->destinationUsedSpaceInKb = $destinationUsedSpaceInKb;
        $this->destinationFreeSpaceInKb = $destinationFreeSpaceInKb;
        $this->timeSpentRunningBackupsInSeconds = $timeSpentRunningBackupsInSeconds;
        $this->errorsInLog = $errorsInLog;
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }
}
