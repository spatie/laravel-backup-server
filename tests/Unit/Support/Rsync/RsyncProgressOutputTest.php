<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Spatie\BackupServer\Tasks\Backup\Support\Rsync\RsyncProgressOutput;

it('can return the transfer speed', function () {
    $output = '     9,846,214  77%   11.96MB/s    0:00:00 (xfr#1317, ir-chk=1014/2680)';

    $rsyncOutput = new RsyncProgressOutput($output);

    expect($rsyncOutput->getTransferSpeed())->toEqual('11.96MB/s');
});

it('can determine that the output concerns progress', function () {
    $output = '     9,846,214  77%   11.96MB/s    0:00:00 (xfr#1317, ir-chk=1014/2680)';

    $rsyncOutput = new RsyncProgressOutput($output);

    expect($rsyncOutput->concernsProgress())->toBeTrue();
    expect($rsyncOutput->isSummary())->toBeFalse();
});

it('can determine that the output is the summary', function () {
    $output = file_get_contents(__DIR__.'/stubs/rsyncSummary.txt');

    $rsyncOutput = new RsyncProgressOutput($output);

    expect($rsyncOutput->isSummary())->toBeTrue();
    expect($rsyncOutput->concernsProgress())->toBeFalse();
});
