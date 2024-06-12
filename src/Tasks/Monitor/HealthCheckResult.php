<?php

namespace Spatie\BackupServer\Tasks\Monitor;

class HealthCheckResult
{
    protected bool $ok = true;

    protected bool $runRemainingChecks = true;

    public static function ok(): self
    {
        return new static;
    }

    public static function failed(string $message)
    {
        return (new static($message))->markAsFailed();
    }

    protected function __construct(protected string $message = '')
    {
    }

    public function isOk(): bool
    {
        return $this->ok;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function markAsFailed(): self
    {
        $this->ok = false;

        return $this;
    }

    public function doNotRunRemainingChecks(): self
    {
        $this->runRemainingChecks = false;

        return $this;
    }

    public function shouldContinueRunningRemainingChecks(): bool
    {
        return $this->runRemainingChecks;
    }
}
