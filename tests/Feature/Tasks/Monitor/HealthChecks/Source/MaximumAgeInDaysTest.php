<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\Source\MaximumAgeInDays;
use Spatie\BackupServer\Tests\Factories\BackupFactory;
use Spatie\TestTime\TestTime;


uses(\Spatie\BackupServer\Tests\Feature\Tasks\Monitor\Concerns\HealthCheckAssertions::class);

beforeEach(function () {
    TestTime::freeze();

    $this->source = Source::factory()->create();

    $this->maximumAgeDaysHealthCheck = new MaximumAgeInDays(1);
});

it('will start failing if there is no backup taken in the configured amount of days', function () {
    $this->assertHealthCheckSucceeds($this->maximumAgeDaysHealthCheck->getResult($this->source));

    TestTime::addDay()->subSecond();
    $this->assertHealthCheckSucceeds($this->maximumAgeDaysHealthCheck->getResult($this->source));

    TestTime::addSecond();
    $this->assertHealthCheckFails($this->maximumAgeDaysHealthCheck->getResult($this->source));
});

it('will start failing if the youngest backup is older then the configured amount of days', function () {
    TestTime::addHours(12);

    (new BackupFactory())->completed()->source($this->source)->create();

    TestTime::addDay()->subSecond();
    $this->assertHealthCheckSucceeds($this->maximumAgeDaysHealthCheck->getResult($this->source->refresh()));

    TestTime::addDay();
    $this->assertHealthCheckFails($this->maximumAgeDaysHealthCheck->getResult($this->source->refresh()));

    (new BackupFactory())->completed()->source($this->source)->create();
    $this->assertHealthCheckSucceeds($this->maximumAgeDaysHealthCheck->getResult($this->source->refresh()));
});

test('the value on the destination overrides the configured amount of days', function () {
    (new BackupFactory())->completed()->source($this->source)->create();

    TestTime::addDay();
    $this->assertHealthCheckFails($this->maximumAgeDaysHealthCheck->getResult($this->source->refresh()));

    $this->source->destination->update(['healthy_maximum_backup_age_in_days_per_source' => 2]);
    $this->assertHealthCheckSucceeds($this->maximumAgeDaysHealthCheck->getResult($this->source->refresh()));

    TestTime::addDay();
    $this->assertHealthCheckFails($this->maximumAgeDaysHealthCheck->getResult($this->source->refresh()));

    $this->source->update(['healthy_maximum_backup_age_in_days' => 3]);
    $this->assertHealthCheckSucceeds($this->maximumAgeDaysHealthCheck->getResult($this->source->refresh()));
});