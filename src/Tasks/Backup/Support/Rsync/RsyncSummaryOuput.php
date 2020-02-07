<?php

namespace Spatie\BackupServer\Tasks\Backup\Support\Rsync;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class RsyncSummaryOuput
{
    private Collection $lines;

    public function __construct(string $output)
    {
        $lines = explode(PHP_EOL, $output);

        $this->lines = collect(array_filter($lines));
    }

    public function averageSpeedInMB(): string
    {
        $line = $this->getValueOfLineLineStartingWith('sent') ?? '';

        $bytesPerSecondString = explode('  ', $line)[2] ?? null;

        if (is_null($bytesPerSecondString)) {
            return '0MB/s';
        }

        $bytesPerSecondString = str_replace(' bytes/sec', '', $bytesPerSecondString, );

        $bytesPerSecondString = str_replace(',', '', $bytesPerSecondString);

        $megaBytesPerSecond = ((float)$bytesPerSecondString / 1024 / 1024);

        return round($megaBytesPerSecond, 2) . 'MB/s';
    }

    protected function getValueOfLineLineStartingWith(string $startsWith): ?string
    {
        return $this->lines->first(fn (string $line) => Str::startsWith($line, $startsWith));
    }

    protected function removeAllNonNumbericalCharacters(string $string): string
    {
        return preg_replace("/[^0-9]/", "", $string);
    }
}
