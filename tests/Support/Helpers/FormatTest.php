<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Spatie\BackupServer\Support\Helpers\Format;
use Spatie\TestTime\TestTime;

it('can format a number as a human readable filesize', function () {
    expect(Format::KbToHumanReadableSize(10))->toEqual('10 KB');
    expect(Format::KbToHumanReadableSize(100))->toEqual('100 KB');
    expect(Format::KbToHumanReadableSize(1000))->toEqual('1000 KB');
    expect(Format::KbToHumanReadableSize(10000))->toEqual('9.77 MB');
    expect(Format::KbToHumanReadableSize(1000000))->toEqual('976.56 MB');
    expect(Format::KbToHumanReadableSize(10000000))->toEqual('9.54 GB');
    expect(Format::KbToHumanReadableSize(10000000000))->toEqual('9.31 TB');
});

it('can determine the age in days', function () {
    TestTime::freeze('Y-m-d H:i:s', '2016-01-01 00:00:00');

    expect(Format::ageInDays(now()->subHour()))->toEqual('0.04 (1 hour ago)');
    expect(Format::ageInDays(now()->subHour()->subDay()))->toEqual('1.04 (1 day ago)');
    expect(Format::ageInDays(now()->subHour()->subMonths(1)))->toEqual('30.04 (4 weeks ago)');
});
