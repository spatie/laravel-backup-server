<?php

namespace Spatie\BackupServer\Tasks\Backup\Support;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use Spatie\BackupServer\Models\Backup;
use Symfony\Component\Process\Process;

class BackupCollection extends Collection
{
    public function realSizeInKb(): int
    {
        return $this->sum(function (Backup $backup) {
            return $backup->real_size_in_kb;
        });
    }

    public function sizeInKb(): int
    {
        return $this->sum(function (Backup $backup) {
            return $backup->size_in_kb;
        });
    }

    public function youngest(): ?Backup
    {
        return $this->first();
    }

    public function oldest(): ?Backup
    {
        return $this->reverse()->last();
    }

    public function recalculateRealSizeInKb(): self
    {
        if ($this->count() === 0) {
            return $this;
        }

        $firstBackup = $this->first();

        $command = 'du -kd 1 ..';

        // `du` on EBS volumes isn't too fast. 5 minutes should be enough for a 150GB backup
        $timeout = 60 * 5;

        $process = Process::fromShellCommandline($command, $firstBackup->destinationLocation()->getFullPath())->setTimeout($timeout);
        $process->run();

        $output = $process->getOutput();

        $this
            ->whereNotNull('path')
            ->each(function (Backup $backup) use ($output) {
                $directoryLine = collect(explode(PHP_EOL, $output))->first(function (string $line) use ($backup) {
                    return Str::contains($line, $backup->destinationLocation()->getDirectory());
                });

                if (! $directoryLine) {
                    $backup->update(['real_size_in_kb' => 0]);

                    return;
                }

                $sizeInKb = Str::before($directoryLine, "\t");

                $backup->update(['real_size_in_kb' => (int)trim($sizeInKb)]);
            });

        return $this;
    }
}
