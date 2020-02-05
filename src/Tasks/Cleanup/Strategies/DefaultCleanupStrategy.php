<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Strategies;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Support\Helpers\Enums\Task;
use Spatie\BackupServer\Tasks\Backup\Support\BackupCollection;
use Spatie\BackupServer\Tasks\Cleanup\Support\DefaultStrategyConfig;
use Spatie\BackupServer\Tasks\Cleanup\Support\Period;

class DefaultCleanupStrategy implements CleanupStrategy
{
    /** @var \Spatie\Backup\BackupDestination\Backup */
    protected ?Backup $youngestBackup;

    private DefaultStrategyConfig $config;

    public function deleteOldBackups(Source $source)
    {
        dump('start deleting...');
        $this->config = new DefaultStrategyConfig($source);

        /** @var \Spatie\BackupServer\Tasks\Backup\Support\BackupCollection $backups */
        $backups = $source->completedBackups()->get();

        // Don't ever delete the youngest backup.
        $this->youngestBackup = $backups->shift();

        $dateRanges = $this->calculateDateRanges();

        $backupsPerPeriod = $dateRanges->map(function (Period $period) use ($backups) {
            return $backups->filter(function (Backup $backup) use ($period) {
                return $backup->created_at->between($period->startDate(), $period->endDate());
            });
        });
        $backupsPerPeriod['daily'] = $this->groupByDateFormat($backupsPerPeriod['daily'], 'Ymd');
        $backupsPerPeriod['weekly'] = $this->groupByDateFormat($backupsPerPeriod['weekly'], 'YW');
        $backupsPerPeriod['monthly'] = $this->groupByDateFormat($backupsPerPeriod['monthly'], 'Ym');
        $backupsPerPeriod['yearly'] = $this->groupByDateFormat($backupsPerPeriod['yearly'], 'Y');

        $this->removeBackupsForAllPeriodsExceptOne($backupsPerPeriod);

        $this->removeBackupsOlderThan($dateRanges['yearly']->endDate(), $backups);

        $backups = $backups->filter->exists;

        if ($sizeLimitInMb = $this->config->deleteOldestBackupsWhenUsingMoreMegabytesThan) {
            $this->removeOldBackupsUntilUsingLessThanMaximumStorage($backups, $sizeLimitInMb);
        }
    }

    protected function calculateDateRanges(): Collection
    {
        $daily = new Period(
            Carbon::now()->subDays($this->config->keepAllBackupsForDays),
            Carbon::now()
                ->subDays($this->config->keepAllBackupsForDays)
                ->subDays($this->config->keepDailyBackupsForDays)
        );

        $weekly = new Period(
            $daily->endDate(),
            $daily->endDate()->subWeeks($this->config->keepWeeklyBackupsForWeeks)
        );

        $monthly = new Period(
            $weekly->endDate(),
            $weekly->endDate()->subMonths($this->config->keepMonthlyBackupsForMonths)
        );

        $yearly = new Period(
            $monthly->endDate(),
            $monthly->endDate()->subYears($this->config->keepYearlyBackupsForYears)
        );

        return collect(compact('daily', 'weekly', 'monthly', 'yearly'));
    }

    protected function groupByDateFormat(Collection $backups, string $dateFormat): Collection
    {
        return $backups->groupBy(function (Backup $backup) use ($dateFormat) {
            return $backup->created_at->format($dateFormat);
        });
    }

    protected function removeBackupsForAllPeriodsExceptOne(Collection $backupsPerPeriod)
    {
        $backupsPerPeriod->each(function (Collection $groupedBackupsByDateProperty) {
            $groupedBackupsByDateProperty->each(function (Collection $group) {
                $group->shift();

                $group->each->delete();
            });
        });
    }

    protected function removeBackupsOlderThan(Carbon $endDate, Collection $backups)
    {
        $backups->filter(function (Backup $backup) use ($endDate) {
            return $backup->created_at->lt($endDate);
        })->each->delete();
    }

    protected function removeOldBackupsUntilUsingLessThanMaximumStorage(BackupCollection $backups, int $sizeLimitInMb)
    {
        if (! $backups->count()) {
            return;
        }

        $actualSizeInKb = $backups
            ->map(function (Backup $backup) {
                $model =  $backup->recalculateRealBackupSize()->refresh();
                return $model;
            })
            ->filter->exists
            ->sum('real_size_in_kb');

        if ($actualSizeInKb <= ($sizeLimitInMb * 1024)) {
            return;
        }

        /** @var \Spatie\BackupServer\Models\Backup $oldestBackup */
        $oldestBackup = $backups->oldest();

        $oldestBackup->logInfo(Task::CLEANUP, 'Deleting backup because destination uses more space than the limit allows');

        $oldestBackup->delete();

        $backups = $backups->filter->exists;

        $this->removeOldBackupsUntilUsingLessThanMaximumStorage($backups, $sizeLimitInMb);
    }
}
