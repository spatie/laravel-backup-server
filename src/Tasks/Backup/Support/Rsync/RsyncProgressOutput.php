<?php

namespace Spatie\BackupServer\Tasks\Backup\Support\Rsync;

use Illuminate\Support\Str;
use Spatie\Regex\Regex;

class RsyncProgressOutput
{
    public function __construct(
        private string $output
    ) {
    }

    public function concernsProgress(): bool
    {
        return Str::contains($this->output, 'xfr#');
    }

    public function isSummary(): bool
    {
        return Str::contains($this->output, 'Number of files');
    }

    public function getTransferSpeed(): string
    {
        return Regex::match('/\d+\.\d+(k|M)B\/s/', $this->output)->resultOr('');
    }

    public function getSummary(): string
    {
        return trim($this->output);
    }
}
