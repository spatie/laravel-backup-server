<?php

namespace Spatie\BackupServer\Tests\Feature\Tasks\Backup\Jobs;

use \Spatie\Docker\DockerContainerInstance;
use Illuminate\Support\Facades\Storage;
use Spatie\BackupServer\Models\Backup;
use Spatie\BackupServer\Models\Destination;
use Spatie\BackupServer\Models\Source;
use Spatie\BackupServer\Tests\TestCase;
use Spatie\Docker\DockerContainer;

class PerformBackupJobTest extends TestCase
{
    private ?DockerContainerInstance $container;

    private ?Source $source;

    private ?Destination $destination;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake('backups');

        $this->container = DockerContainer::create('spatie/laravel-backup-server-tests')
            ->name('laravel-backup-server-tests')
            ->mapPort(4848, 22)
            ->stopOnDestruct()
            ->start()
            ->addPublicKey($this->publicKeyPath());

        $this->source = factory(Source::class)->create([
            'host' => '0.0.0.0',
            'ssh_port' => '4848',
            'ssh_user' => 'root',
            'ssh_private_key_file' => $this->privateKeyPath(),
            'includes' => ['/src'],
            'backup_hour' => now()->hour,
        ]);
    }

    /** @test */
    public function it_can_perform_a_backup()
    {
        $this->container->addFiles(__DIR__ . '/stubs/serverContent/testServer', '/src');

        $this->artisan('backup-server:backup')->assertExitCode(0);

        $this->assertTrue($this->source->backups()->first()->has('src/1.txt'));

        $this->assertEquals(Backup::STATUS_COMPLETED, $this->source->backups()->first()->status);
    }

    /** @test */
    public function it_can_perform_a_pre_backup_command()
    {
        $this->container->addFiles(__DIR__ . '/stubs/serverContent/testServer', '/src');

        $this->source->update(['pre_backup_commands' => ['cd /src', 'touch newfile.txt']]);

        $this->artisan('backup-server:backup')->assertExitCode(0);

        $this->assertTrue($this->source->backups()->first()->has('src/newfile.txt'));

        $this->assertEquals(Backup::STATUS_COMPLETED, $this->source->backups()->first()->status);
    }

    /** @test */
    public function it_will_mark_the_backup_as_failed_if_the_pre_backup_commands_cannot_execute()
    {
        $this->container->addFiles(__DIR__ . '/stubs/serverContent/testServer', '/src');

        $this->source->update(['pre_backup_commands' => ['this-is-a-non-valid-command']]);

        $this->artisan('backup-server:backup')->assertExitCode(0);

        $this->assertEquals(Backup::STATUS_FAILED, $this->source->backups()->first()->status);
    }

    /** @test */
    public function it_can_perform_post_backup_commands()
    {
        $this->container->addFiles(__DIR__ . '/stubs/serverContent/testServer', '/src');

        $this->source->update(['post_backup_commands' => ['echo "ok" >> /post_backup_command.txt']]);

        $this->artisan('backup-server:backup')->assertExitCode(0);

        $process = $this->container->execute('cat /post_backup_command.txt');

        $this->assertEquals("ok", trim($process->getOutput()));

        $this->assertEquals(Backup::STATUS_COMPLETED, $this->source->backups()->first()->status);
    }

    /** @test */
    public function it_will_mark_the_backup_as_failed_if_the_post_backup_commands_cannot_execute()
    {
        $this->source->update(['post_backup_commands' => ['invalid-command']]);

        $this->artisan('backup-server:backup')->assertExitCode(0);

        $this->assertEquals(Backup::STATUS_FAILED, $this->source->backups()->first()->status);
    }

    /** @test */
    public function it_will_fail_if_the_source_is_not_reachable()
    {
        $this->source->update(['host' => 'non-existing-host']);

        $this->artisan('backup-server:backup')->assertExitCode(0);

        $this->assertEquals(Backup::STATUS_FAILED, $this->source->backups()->first()->status);
    }

    /** @test */
    public function it_will_fail_if_it_cannot_login()
    {
        $this->source->update(['ssh_private_key_file' => null]);

        $this->artisan('backup-server:backup')->assertExitCode(0);

        $this->assertEquals(Backup::STATUS_FAILED, $this->source->backups()->first()->status);
    }

    public function tearDown(): void
    {
        parent::tearDown();

        $this->container->stop();
    }
}
