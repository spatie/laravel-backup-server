<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
it('lists destinations', function () {
    $this->artisan('backup-server:list-destinations')->assertExitCode(0);
});
