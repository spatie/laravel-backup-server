<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Illuminate\Support\Facades\Queue;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Source;
use Spatie\TestTime\TestTime;


it('will dispatch a backup job at the correct time', function () {
    TestTime::freeze('Y-m-d H:i', '2020-01-01 00:00');

    Queue::fake();

    $this->source = Source::factory()->create([
        'cron_expression' => '0 2 * * *',
    ]);

    $this->artisan('backup-server:dispatch-backups');
    expect(Backup::all())->toHaveCount(0);

    TestTime::addHour();
    $this->artisan('backup-server:dispatch-backups');
    expect(Backup::all())->toHaveCount(0);

    TestTime::addHour();
    $this->artisan('backup-server:dispatch-backups');
    expect(Backup::all())->toHaveCount(1);
});
