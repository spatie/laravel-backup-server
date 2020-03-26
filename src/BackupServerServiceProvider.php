<?php

namespace Spatie\BackupServer;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Spatie\BackupServer\Commands\DispatchPerformBackupJobsCommand;
use Spatie\BackupServer\Commands\DispatchPerformCleanupJobsCommand;
use Spatie\BackupServer\Commands\FindContentCommand;
use Spatie\BackupServer\Commands\FindFilesCommand;
use Spatie\BackupServer\Commands\ListDestinationsCommand;
use Spatie\BackupServer\Commands\ListSourcesCommand;
use Spatie\BackupServer\Commands\MonitorBackupsCommand;
use Spatie\BackupServer\Notifications\EventHandler;

class BackupServerServiceProvider extends EventServiceProvider
{
    public function boot()
    {
        parent::boot();

        $this
            ->bootCarbon()
            ->bootCommands()
            ->bootGate()
            ->bootPublishables();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/backup-server.php', 'backup-server');

        $this->app['events']->subscribe(EventHandler::class);
    }

    protected function bootCarbon()
    {
        $dataFormat = config('backup-server.date_format');

        Carbon::macro('toBackupServerFormat', fn () => self::this()->copy()->format($dataFormat));

        return $this;
    }

    protected function bootCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                DispatchPerformBackupJobsCommand::class,
                DispatchPerformCleanupJobsCommand::class,
                ListSourcesCommand::class,
                ListDestinationsCommand::class,
                MonitorBackupsCommand::class,
                FindFilesCommand::class,
                FindContentCommand::class,
            ]);
        }

        return $this;
    }


    protected function bootGate()
    {
        Gate::define('viewBackupServer', fn ($user = null) => app()->environment('local'));

        return $this;
    }

    protected function bootPublishables()
    {
        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/backup-server'),
        ], 'backup-server-views');

        if (! class_exists('CreateBackupServerTables')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_backup_server_tables.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_backup_server_tables.php'),
            ], 'backup-server-migrations');
        }

        return $this;
    }
}
