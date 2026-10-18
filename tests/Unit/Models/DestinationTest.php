<?php

use Illuminate\Support\Facades\Queue;
use Spatie\BackupServer\Enums\DestinationStatus;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Tasks\Cleanup\Jobs\DeleteDestinationJob;
use Spatie\BackupServer\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $this->destination = Destination::factory()->create();
});

it('can get the inode usage percentage', function () {
    $inodeUsagePercentage = $this->destination->getInodeUsagePercentage();

    expect($inodeUsagePercentage)->toBeGreaterThanOrEqual(0);
    expect($inodeUsagePercentage)->toBeLessThanOrEqual(100);
});

it('can get the free space in kb', function () {
    $freeSpaceInMb = $this->destination->getFreeSpaceInKb();

    expect($freeSpaceInMb)->toBeGreaterThan(0);
});

it('can get used space in percentage', function () {
    $usedSpaceInPercentage = $this->destination->getUsedSpaceInPercentage();

    expect($usedSpaceInPercentage)->toBeGreaterThanOrEqual(0);
    expect($usedSpaceInPercentage)->toBeLessThanOrEqual(100);
});

it('can delete a destination in an async way', function () {
    $this->destination->asyncDelete();

    expect(Destination::get())->toHaveCount(0);
});

test('an async delete of a destination will get queued', function () {
    Queue::fake();

    $this->destination->asyncDelete();

    expect(Destination::get())->toHaveCount(1);
    Queue::assertPushed(DeleteDestinationJob::class);
});

it('knows a destination will be deleted after reloading it from the database', function () {
    Queue::fake();

    expect($this->destination->fresh()->willBeDeleted())->toBeFalse();

    $this->destination->asyncDelete();

    $destination = $this->destination->fresh();

    expect($destination->status)->toBe(DestinationStatus::Deleting);
    expect($destination->willBeDeleted())->toBeTrue();
});
