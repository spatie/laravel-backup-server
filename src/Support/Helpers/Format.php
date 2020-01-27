<?php

namespace Spatie\BackupServer\Support\Helpers;

use Carbon\Carbon;

class Format
{
    public static function emoji(bool $bool): string
    {
        if ($bool) {
            return '✅';
        }

        return '❌';
    }

    public static function humanReadableSize(float $sizeInKilobytes): string
    {
        $sizeInBytes = $sizeInKilobytes * 1024;

        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        if ($sizeInBytes === 0) {
            return '0 '.$units[1];
        }
        for ($i = 0; $sizeInBytes > 1024; $i++) {
            $sizeInBytes /= 1024;
        }

        return round($sizeInBytes, 2).' '.$units[$i];
    }

    public static function ageInDays(Carbon $date): string
    {
        return number_format(round($date->diffInMinutes() / (24 * 60), 2), 2).' ('.$date->diffForHumans().')';
    }
}
