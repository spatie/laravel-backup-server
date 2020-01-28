<?php

namespace Spatie\BackupServer\Tests\Feature\Tasks\Monitor\HealthChecks\Source;

use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\Source\MaximumAgeInDays;
use Spatie\BackupServer\Tests\Factories\BackupFactory;
use Spatie\BackupServer\Tests\Feature\Tasks\Monitor\Concerns\HealthCheckAssertions;
use Spatie\BackupServer\Tests\TestCase;
use Spatie\TestTime\TestTime;

class MaximumAgeInDaysTest extends TestCase
{
    use HealthCheckAssertions;

    private ?Source $source;

    private MaximumAgeInDays $maximumAgeDaysHealthCheck;

    public function setUp(): void
    {
        parent::setUp();

        TestTime::freeze();

        $this->source = factory(Source::class)->create();

        $this->maximumAgeDaysHealthCheck = new MaximumAgeInDays(1);
    }

    /** @test */
    public function it_will_start_failing_if_there_is_no_backup_taken_in_the_configured_amount_of_days()
    {
        $this->assertHealthCheckSucceeds($this->maximumAgeDaysHealthCheck->getResult($this->source));

        TestTime::addDay()->subSecond();
        $this->assertHealthCheckSucceeds($this->maximumAgeDaysHealthCheck->getResult($this->source));

        TestTime::addSecond();
        $this->assertHealthCheckFails($this->maximumAgeDaysHealthCheck->getResult($this->source));
    }

    /** @test */
    public function it_will_start_failing_if_the_youngest_backup_is_older_then_the_configured_amount_of_days()
    {
        TestTime::addHours(12);

        (new BackupFactory())->completed()->source($this->source)->create();

        TestTime::addDay()->subSecond();
        $this->assertHealthCheckSucceeds($this->maximumAgeDaysHealthCheck->getResult($this->source->refresh()));

        TestTime::addDay();
        $this->assertHealthCheckFails($this->maximumAgeDaysHealthCheck->getResult($this->source->refresh()));

        (new BackupFactory())->completed()->source($this->source)->create();
        $this->assertHealthCheckSucceeds($this->maximumAgeDaysHealthCheck->getResult($this->source->refresh()));
    }
}
