<?php

namespace Spatie\BackupServer\Tasks\Summary\Actions;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tasks\Summary\ServerSummary;

class CreateServerSummaryAction
{
    public function execute(Carbon $from, Carbon $to): ServerSummary
    {
        $backupsQuery = config('backup-server.backup_model')::query()->where(function (Builder $query) use ($to, $from) {
            $query
                ->whereBetween('completed_at', [$from, $to])
                ->orWhereBetween('created_at', [$from, $to]);
        });

        $destinations = config('backup-server.backup_destination_model')::get();
        $healthyDestinations = $destinations->filter(fn (Destination $destination) => $destination->isHealthy());

        $totalUsedSpaceInKb = $destinations->sum(fn (Destination $destination) => $destination->backups->realSizeInKb());
        $totalFreeSpaceInKb = $destinations->sum(fn (Destination $destination) => $destination->getFreeSpaceInKb());

        return new ServerSummary(
            $from,
            $to,
            (clone $backupsQuery)->completed()->count(),
            (clone $backupsQuery)->failed()->count(),
            $healthyDestinations->count(),
            $destinations->count() - $healthyDestinations->count(),
            config('backup-server.backup_source_model')::healthy()->count(),
            config('backup-server.backup_source_model')::unhealthy()->count(),
            $totalUsedSpaceInKb,
            $totalFreeSpaceInKb,
            $backupsQuery->sum('rsync_time_in_seconds'),
            config('backup-server.backup_log_item_model')::query()->where('level', 'error')->count(),
        );
    }
}
