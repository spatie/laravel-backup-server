<?php


namespace Spatie\BackupServer\Tests\Feature\Tasks\Monitor\Concerns;

use Spatie\BackupServer\Tasks\Monitor\HealthCheckResult;

trait HealthCheckAssertions
{
    protected function assertHealthCheckSucceeds(HealthCheckResult $healthCheckResult)
    {
        $this->assertTrue($healthCheckResult->isOk(), 'A health check, that was expected to succeed, has failed.');
    }

    protected function assertHealthCheckFails(HealthCheckResult $healthCheckResult)
    {
        $this->assertFalse($healthCheckResult->isOk(), 'A health check, that was expected to fail, has succeeded.');
    }
}
