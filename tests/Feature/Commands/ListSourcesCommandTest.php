<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Spatie\BackupServer\Exceptions\InvalidCommandInput;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Tests\Database\Factories\SourceFactory;

it('lists sources', function () {
    $this->artisan('backup-server:list')->assertExitCode(0);
});

it('lists sources when sorted', function () {
    $healthySource = SourceFactory::new(['healthy' => true])->create();
    $backup = Backup::factory()->create(['size_in_kb' => 10]);
    $backup->source()->associate($healthySource);

    SourceFactory::new(['healthy' => false])->create();

    foreach ($this->options as $option) {
        $this->artisan("backup-server:list --sortBy={$option}")->assertExitCode(0);
    }

    $this->artisan('backup-server:list --desc')->assertExitCode(0);
    $this->artisan('backup-server:list -D')->assertExitCode(0);
    $this->artisan('backup-server:list --sortBy=name --desc')->assertExitCode(0);
});

it('fails with invalid sort value', function () {
    $this->expectException(InvalidCommandInput::class);
    $this->expectExceptionMessage('`unknown` is not a valid option. Use one of these options: name, id, healthy, backup_count, newest_backup, youngest_backup_size, backup_size, used_storage');

    $this->artisan('backup-server:list --sortBy=unknown')->assertExitCode(0);
});