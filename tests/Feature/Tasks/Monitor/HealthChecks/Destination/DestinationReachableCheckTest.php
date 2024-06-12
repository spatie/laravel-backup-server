<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tasks\Monitor\HealthChecks\Destination\DestinationReachable;

uses(\Spatie\BackupServer\Tests\Feature\Tasks\Monitor\Concerns\HealthCheckAssertions::class);

it('will pass when the destination is reachable', function () {
    $destination = Destination::factory()->create();

    $checkResult = (new DestinationReachable())->getResult($destination);

    $this->assertHealthCheckSucceeds($checkResult);
});

it('will fail when the destination is not reachable', function () {
    $destination = Destination::factory()->create(['disk_name' => 'non-existing-disk']);

    $checkResult = (new DestinationReachable())->getResult($destination);

    $this->assertHealthCheckFails($checkResult);
});
