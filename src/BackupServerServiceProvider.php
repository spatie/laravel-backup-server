<?php

namespace Spatie\BackupServer;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Spatie\BackupServer\Commands\DispatchPerformBackupJobsCommand;
use Spatie\BackupServer\Commands\DispatchPerformCleanupJobsCommand;
use Spatie\BackupServer\Http\App\Middleware\SetBackupServerDefaults;
use Spatie\BackupServer\Http\Middleware\Authorize;
use Spatie\BladeX\Facades\BladeX;
use Spatie\Mailcoach\Commands\CalculateStatisticsCommand;
use Spatie\Mailcoach\Commands\DeleteOldUnconfirmedSubscribersCommand;
use Spatie\Mailcoach\Commands\RetryPendingSendsCommand;
use Spatie\Mailcoach\Commands\SendCampaignSummaryMailCommand;
use Spatie\Mailcoach\Commands\SendEmailListSummaryMailCommand;
use Spatie\Mailcoach\Commands\SendScheduledCampaignsCommand;
use Spatie\Mailcoach\Events\CampaignSentEvent;
use Spatie\Mailcoach\Http\App\Controllers\HomeController;
use Spatie\Mailcoach\Http\App\ViewComposers\FooterComposer;
use Spatie\Mailcoach\Http\App\ViewComposers\QueryStringComposer;
use Spatie\Mailcoach\Http\App\ViewModels\BladeX\DateTimeFieldViewModel;
use Spatie\Mailcoach\Http\App\ViewModels\BladeX\FilterViewModel;
use Spatie\Mailcoach\Http\App\ViewModels\BladeX\ReplacerHelpTextsViewModel;
use Spatie\Mailcoach\Http\App\ViewModels\BladeX\SearchViewModel;
use Spatie\Mailcoach\Http\App\ViewModels\BladeX\THViewModel;
use Spatie\Mailcoach\Listeners\SendCampaignSentEmail;
use Spatie\Mailcoach\Support\HttpClient;
use Spatie\Mailcoach\Support\Version;
use Spatie\QueryString\QueryString;

class BackupServerServiceProvider extends EventServiceProvider
{
    public function boot()
    {
        parent::boot();

        $this
            ->bootCarbon()
            ->bootCommands()
            ->bootGate()
            ->bootPublishables()
            ->bootRoutes()
            ->bootViews();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/backup-server.php', 'backup-server');

        /*
        $this->app->singleton(QueryString::class, fn () => new QueryString(urldecode($this->app->request->getRequestUri())));

        $this->app->singleton(Version::class, function () {
            $httpClient = new HttpClient();

            return new Version($httpClient);
        });
        */
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

        if (!class_exists('CreateBackupServerTables')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_backup_server_tables.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_backup_server_tables.php'),
            ], 'backup-server-migrations');
        }

        return $this;
    }

    protected function bootRoutes()
    {
        Route::macro('backupServer', function (string $url = '') {
            Route::prefix($url)->group(function () {
                Route::middleware(['web', Authorize::class, SetBackupServerDefaults::class])->group(__DIR__ . '/../routes/backup-server.php');
            });
        });

        return $this;
    }

    protected function bootViews()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'backup-server');

        return $this;
    }
}
