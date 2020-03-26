<?php

namespace Spatie\BackupServer\Tests;

use CreateBackupServerTables;
use CreateUsersTable;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\BackupServer\BackupServerServiceProvider;
use Spatie\TestTime\TestTime;

class TestCase extends Orchestra
{
    public function setUp(): void
    {
        parent::setUp();

        $this->withFactories(__DIR__.'/database/factories');

        Route::backupServer('backups');

        $this->withoutExceptionHandling();

        Gate::define('viewBackupServer', fn () => true);

        TestTime::freeze();
    }

    protected function getPackageProviders($app)
    {
        return [
            BackupServerServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('filesystems.disks.backups', [
            'driver' => 'local',
            'root' => storage_path('app/backups'),
        ]);

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        include_once __DIR__.'/../database/migrations/create_backup_server_tables.php.stub';
        (new CreateBackupServerTables())->up();

        include_once __DIR__.'/database/migrations/create_users_table.php.stub';
        (new CreateUsersTable())->up();
    }

    public function authenticate()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user);
    }

    protected function configuredNotifiable()
    {
        $notifiableClass = config('backup-server.notifications.notifiable');

        return app($notifiableClass);
    }

    public function publicKeyPath(): string
    {
        return __DIR__ . '/docker/keys/laravel_backup_server_id_rsa.pub';
    }

    public function privateKeyPath(): string
    {
        $keyPath = __DIR__ . '/docker/keys/laravel_backup_server_id_rsa';

        chmod($keyPath, 0700);

        return $keyPath;
    }

    public function runningOnGitHubActions(): bool
    {
        return env('GITHUB_WORKFLOW') !== null;
    }
}
