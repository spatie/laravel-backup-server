<?php

namespace Spatie\BackupServer\Tasks\Backup\Support\BackupScheduler;

use Spatie\BackupServer\Models\Source;
use Illuminate\Support\Str;

class DefaultBackupScheduler implements BackupScheduler
{
    public function shouldBackupNow(Source $source): bool
    {
        // Backup every hour
        if ($source->backup_hour === '*') {
            return true;
        }

        $currentHour = now()->hour;

        // Backup one specific hour: [0-23]
        if (is_numeric($source->backup_hour)) {
            return $currentHour === (int) $source->backup_hour;
        }

        // Backup multiple hours: e.g. 0;1;5;10;13-17
        $hours = explode(';', $source->backup_hour);
        foreach ($hours as $hour) {
            // Backup one specific hour: [0-23]
            if (is_numeric($hour) && $currentHour === (int) $hour) {
                return true;
            }

            // Backup in timespan
            if (Str::contains($hour, '-')) {
                $bounds = explode('-', $hour);

                $leftBound = (int) $bounds[0];
                $rightBound = (int) $bounds[1];

                // If someone would note something link 22-5
                if ($leftBound > $rightBound) {
                    if ($currentHour >= $leftBound || $currentHour <= $rightBound) {
                        return true;
                    }
                } else {
                    // This would match something like 5-23
                    if ($currentHour >= $leftBound && $currentHour <= $rightBound) {
                        return true;
                    }
                }
            }
        }

        // Fallback
        return false;
    }
}
