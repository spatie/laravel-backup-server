<?php

namespace Spatie\BackupServer\Tasks\Cleanup\Support;

use Carbon\Carbon;

class Period
{
    public function __construct(
        protected Carbon $startDate,
        protected Carbon $endDate
    ) {}

    public function startDate(): Carbon
    {
        return $this->startDate->copy();
    }

    public function endDate(): Carbon
    {
        return $this->endDate->copy();
    }
}
