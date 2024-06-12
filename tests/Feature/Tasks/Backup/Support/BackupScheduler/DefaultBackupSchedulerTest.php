<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Illuminate\Support\Carbon;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tasks\Backup\Support\BackupScheduler\BackupScheduler;

it('runs a backup if the cron expression is due', function () {
    Carbon::setTestNow(now()->setTime(2, 0));

    $source = Source::factory()->create(['cron_expression' => '0 2 * * *']);

    expect(app(BackupScheduler::class)->shouldBackupNow($source))->toBeTrue();
});

it('does not run a backup if the cron expression is not due', function () {
    Carbon::setTestNow(now()->setTime(0, 0));

    $source = Source::factory()->create(['cron_expression' => '0 2 * * *']);

    expect(app(BackupScheduler::class)->shouldBackupNow($source))->toBeFalse();
});
