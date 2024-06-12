<?php

uses(\Spatie\BackupServer\Tests\TestCase::class);
use Illuminate\Support\Carbon;
use Spatie\BackupServer\Enums\BackupStatus;
use Spatie\BackupServer\Models\Source;
use Spatie\Docker\DockerContainer;

beforeEach(function () {
    Carbon::setTestNow(now()->setTime(2, 0));

    //Storage::fake('backups');
    $this->container = DockerContainer::create('spatie/laravel-backup-server-tests')
        ->name('laravel-backup-server-tests')
        ->mapPort(4848, 22)
        ->stopOnDestruct()
        ->start()
        ->addPublicKey($this->publicKeyPath());

    $this->source = Source::factory()->create([
        'host' => '0.0.0.0',
        'ssh_port' => '4848',
        'ssh_user' => 'root',
        'ssh_private_key_file' => $this->privateKeyPath(),
        'includes' => ['/src'],
        'excludes' => ['exclude.txt'],
        'cron_expression' => '0 2 * * *',
    ]);
});

it('can perform a backup', function () {
    $this->container->addFiles(__DIR__.'/stubs/serverContent/testServer', '/src');

    $this->artisan('backup-server:dispatch-backups')->assertExitCode(0);

    expect($this->source->backups()->first()->status)->toBe(BackupStatus::Completed);
    expect($this->source->backups()->first()->has('src/1.txt'))->toBeTrue();
    expect($this->source->backups()->first()->has('src/exclude.txt'))->toBeFalse();
});

it('can perform a pre backup command', function () {
    $this->container->addFiles(__DIR__.'/stubs/serverContent/testServer', '/src');

    $this->source->update(['pre_backup_commands' => ['cd /src', 'touch newfile.txt']]);

    $this->artisan('backup-server:dispatch-backups')->assertExitCode(0);

    expect($this->source->backups()->first()->has('src/newfile.txt'))->toBeTrue();

    expect($this->source->backups()->first()->status)->toBe(BackupStatus::Completed);
});

it('will mark the backup as failed if the pre backup commands cannot execute', function () {
    $this->container->addFiles(__DIR__.'/stubs/serverContent/testServer', '/src');

    $this->source->update(['pre_backup_commands' => ['this-is-a-non-valid-command']]);

    $this->artisan('backup-server:dispatch-backups')->assertExitCode(0);

    expect($this->source->backups()->first()->status)->toBe(BackupStatus::Failed);
});

it('can perform post backup commands', function () {
    $this->container->addFiles(__DIR__.'/stubs/serverContent/testServer', '/src');

    $this->source->update(['post_backup_commands' => ['echo "ok" >> /post_backup_command.txt']]);

    $this->artisan('backup-server:dispatch-backups')->assertExitCode(0);

    $process = $this->container->execute('cat /post_backup_command.txt');

    expect(trim($process->getOutput()))->toBe('ok');

    expect($this->source->backups()->first()->status)->toBe(BackupStatus::Completed);
});

it('will mark the backup as failed if the post backup commands cannot execute', function () {
    $this->source->update(['post_backup_commands' => ['invalid-command']]);

    $this->artisan('backup-server:dispatch-backups')->assertExitCode(0);

    expect($this->source->backups()->first()->status)->toBe(BackupStatus::Failed);
});

it('will fail if the source is not reachable', function () {
    $this->source->update(['host' => 'non-existing-host']);

    $this->artisan('backup-server:dispatch-backups')->assertExitCode(0);

    expect($this->source->backups()->first()->status)->toBe(BackupStatus::Failed);
});

it('will fail if it cannot login', function () {
    $this->source->update(['ssh_private_key_file' => null]);

    $this->artisan('backup-server:dispatch-backups')->assertExitCode(0);

    expect($this->source->backups()->first()->status)->toBe(BackupStatus::Failed);
});

afterEach(function () {
    $this->container->stop();
});
