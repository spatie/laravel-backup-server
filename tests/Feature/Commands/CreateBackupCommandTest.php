<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Spatie\BackupServer\Models\Source;

it('can immediately perform a backup', function () {
    $source = Source::factory()->create();

    $this->artisan('backup-server:backup', [
        'sourceName' => $source->name,
    ])->assertExitCode(0)->expectsOutput('Ensuring source is reachable...');
});
