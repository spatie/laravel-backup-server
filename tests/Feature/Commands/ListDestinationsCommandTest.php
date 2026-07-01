<?php

use Spatie\BackupServer\Tests\TestCase;

uses(TestCase::class);
it('lists destinations', function () {
    $this->artisan('backup-server:list-destinations')->assertExitCode(0);
});
