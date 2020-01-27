<?php

namespace Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination;

class MaximumInodeUsageInPercentage
{
    private int $maximumPercentage;

    public function __construct(int $maximumPercentage)
    {
        $this->maximumPercentage = $maximumPercentage;
    }

    public function passes()
    {
    }
}
