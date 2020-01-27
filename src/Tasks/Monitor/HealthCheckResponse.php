<?php

namespace Spatie\BackupServer\Tasks\Monitor;

class HealthCheckResponse
{
    protected bool $passes = true;

    protected string $message = '';

    public static function passes()
    {
        return new static();
    }

    public static function fails(string $message)
    {
        return (new static($message))->markAsFailed();
    }

    protected function __construct($message = '')
    {
        $this->message = $message;
    }

    public function markAsFailed(): self
    {
        $this->passes = false;

        return $this;
    }
}
