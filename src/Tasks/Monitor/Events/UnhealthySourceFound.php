<?php

namespace Spatie\BackupServer\Tasks\Monitor\Events;

use Spatie\BackupServer\Models\Source;

class UnhealthySourceFound
{
    public Source $source;

    public array $failureMessages;

    public function __construct(Source $source, array $failureMessages)
    {
        $this->source = $source;

        $this->failureMessages = $failureMessages;
    }
}
