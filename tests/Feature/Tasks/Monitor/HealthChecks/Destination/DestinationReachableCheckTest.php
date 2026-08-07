<?php

uses(TestCase::class);
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination\DestinationReachable;
use Spatie\BackupServer\Tests\Feature\Tasks\Monitor\Concerns\HealthCheckAssertions;
use Spatie\BackupServer\Tests\TestCase;

uses(HealthCheckAssertions::class);

it('will pass when the destination is reachable', function () {
    $destination = Destination::factory()->create();

    $checkResult = (new DestinationReachable)->getResult($destination);

    $this->assertHealthCheckSucceeds($checkResult);
});

it('will fail when the destination is not reachable', function () {
    $destination = Destination::factory()->create(['disk_name' => 'non-existing-disk']);

    $checkResult = (new DestinationReachable)->getResult($destination);

    $this->assertHealthCheckFails($checkResult);
});
