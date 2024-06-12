<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Spatie\BackupServer\Tasks\Backup\Support\Rsync\RsyncSummaryOutput;

beforeEach(function () {
    $summary = file_get_contents(__DIR__.'/stubs/rsyncSummary.txt');

    $this->rsyncSummary = new RsyncSummaryOutput($summary);
});

it('can get the average speed in mb', function () {
    expect($this->rsyncSummary->averageSpeedInMB())->toEqual('5.17MB/s');
});

it('returns 0 when the average speed could not be determined', function () {
    $rsyncSummary = new RsyncSummaryOutput('invalid-summary');

    expect($rsyncSummary->averageSpeedInMB())->toEqual('0MB/s');
});
