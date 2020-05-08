<?php

namespace Spatie\BackupServer\Tasks\Summary\Actions;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\BackupLogItem;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Summary\ServerSummary;

class CreateServerSummaryAction
{
    public function execute(Carbon $from, Carbon $to): ServerSummary
    {
        $backupsQuery = Backup::query()->where(function (Builder $query) use ($to, $from) {
            $query
                ->whereBetween('completed_at', [$from, $to])
                ->orWhereBetween('created_at', [$from, $to]);
        });
        $destinations = Destination::get();
        $healthyDestinations = $destinations->filter(fn (Destination $destination) => $destination->isHealthy());
        $totalUsedSpaceInKb = $destinations->sum(fn (Destination $destination) => $destination->backups->realSizeInKb());
        $totalFreeSpaceInKb = $destinations->sum(fn (Destination $destination) => $destination->getFreeSpaceInKb());

        return new ServerSummary(
            $from,
            $to,
            $backupsQuery->completed()->count(),
            $backupsQuery->failed()->count(),
            $healthyDestinations->count(),
            $destinations->count() - $healthyDestinations->count(),
            Source::healthy()->count(),
            Source::unhealthy()->count(),
            $totalUsedSpaceInKb,
            $totalFreeSpaceInKb,
            $backupsQuery->sum('rsync_time_in_seconds'),
            BackupLogItem::query()->where('level', 'error')->count(),
        );
    }
}
