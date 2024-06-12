<?php

namespace Spatie\BackupServer;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Spatie\BackupServer\Commands\CreateBackupCommand;
use Spatie\BackupServer\Commands\DispatchPerformBackupJobsCommand;
use Spatie\BackupServer\Commands\DispatchPerformCleanupJobsCommand;
use Spatie\BackupServer\Commands\FindContentCommand;
use Spatie\BackupServer\Commands\FindFilesCommand;
use Spatie\BackupServer\Commands\ListDestinationsCommand;
use Spatie\BackupServer\Commands\ListSourcesCommand;
use Spatie\BackupServer\Commands\MonitorBackupsCommand;
use Spatie\BackupServer\Notifications\EventHandler;
use Spatie\BackupServer\Tasks\Backup\Support\BackupScheduler\BackupScheduler;
use Spatie\BackupServer\Tasks\Cleanup\Strategies\CleanupStrategy;

class BackupServerServiceProvider extends EventServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        $this
            ->bootCarbon()
            ->bootCommands()
            ->bootGate()
            ->bootPublishables()
            ->bootTranslations();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/backup-server.php', 'backup-server');

        $this->app['events']->subscribe(EventHandler::class);

        $this->app->bind(BackupScheduler::class, function () {
            $schedulerClass = config('backup-server.backup.scheduler');

            return new $schedulerClass();
        });

        $this->app->bind(CleanupStrategy::class, function () {
            $strategyClass = config('backup-server.cleanup.strategy');

            return new $strategyClass();
        });
    }

    protected function bootCarbon(): static
    {
        $dataFormat = config('backup-server.date_format');

        Carbon::macro('toBackupServerFormat', fn () => self::this()->copy()->format($dataFormat));

        return $this;
    }

    protected function bootCommands(): static
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateBackupCommand::class,
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

    protected function bootGate(): self
    {
        Gate::define('viewBackupServer', fn ($user = null) => app()->environment('local'));

        return $this;
    }

    protected function bootPublishables(): self
    {
        $this->publishes([
            __DIR__.'/../config/backup-server.php' => config_path('backup-server.php'),
        ], 'backup-server-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/backup-server'),
        ], 'backup-server-views');

        if (! class_exists('CreateBackupServerTables')) {
            $this->publishes([
                __DIR__.'/../database/migrations/create_backup_server_tables.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_backup_server_tables.php'),
            ], 'backup-server-migrations');
        }

        return $this;
    }

    protected function bootTranslations(): self
    {
        $this->loadTranslationsFrom(__DIR__.'/../resources/lang/', 'backup-server');

        return $this;
    }
}
