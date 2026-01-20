<?php

namespace Spatie\LaravelIgnition;

use Illuminate\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Laravel\Octane\Events\RequestReceived;
use Laravel\Octane\Events\RequestTerminated;
use Laravel\Octane\Events\TaskReceived;
use Laravel\Octane\Events\TickReceived;
use Spatie\ErrorSolutions\Contracts\SolutionProviderRepository as SolutionProviderRepositoryContract;
use Spatie\ErrorSolutions\SolutionProviderRepository;
use Spatie\FlareClient\Flare;
use Spatie\Ignition\Config\FileConfigManager;
use Spatie\Ignition\Contracts\ConfigManager;
use Spatie\Ignition\Ignition;
use Spatie\Ignition\IgnitionConfig;
use Spatie\Ignition\IgnitionProvider;
use Spatie\LaravelIgnition\Commands\SolutionMakeCommand;
use Spatie\LaravelIgnition\Commands\SolutionProviderMakeCommand;
use Spatie\LaravelIgnition\Commands\TestCommand;
use Spatie\LaravelIgnition\ContextProviders\LaravelContextProviderDetector;
use Spatie\LaravelIgnition\Exceptions\InvalidConfig;
use Spatie\LaravelIgnition\FlareMiddleware\AddJobs;
use Spatie\LaravelIgnition\FlareMiddleware\AddLogs;
use Spatie\LaravelIgnition\FlareMiddleware\AddQueries;
use Spatie\LaravelIgnition\Recorders\DumpRecorder\DumpRecorder;
use Spatie\LaravelIgnition\Recorders\JobRecorder\JobRecorder;
use Spatie\LaravelIgnition\Recorders\LogRecorder\LogRecorder;
use Spatie\LaravelIgnition\Recorders\QueryRecorder\QueryRecorder;
use Spatie\LaravelIgnition\Renderers\IgnitionExceptionRenderer;
use Spatie\LaravelIgnition\Support\FlareLogHandler;
use Spatie\LaravelIgnition\Support\LaravelDocumentationLinkFinder;
use Spatie\LaravelIgnition\Support\SentReports;
use Spatie\LaravelIgnition\Views\ViewExceptionMapper;
use function Laravel\Prompts\confirm;

class IgnitionServiceProvider extends ServiceProvider
{
    protected IgnitionProvider $provider;

    public function register(): void
    {
        $this->registerConfig();
        $this->registerIgnition();
        $this->registerRenderer();
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
            $this->publishConfigs();
        }

        $this->provider->boot();

        $this->registerRoutes();
    }

    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/ignition.php', 'ignition');
    }

    protected function registerCommands(): void
    {
        if ($this->app['config']->get('ignition.register_commands')) {
            $this->commands([
                SolutionMakeCommand::class,
                SolutionProviderMakeCommand::class,
            ]);
        }
    }

    protected function publishConfigs(): void
    {
        $this->publishes([
            __DIR__.'/../config/ignition.php' => config_path('ignition.php'),
        ], 'ignition-config');
    }

    protected function registerRenderer(): void
    {
        $this->app->bind(
            'Illuminate\Contracts\Foundation\ExceptionRenderer',
            fn (Application $app) => $app->make(IgnitionExceptionRenderer::class)
        );
    }

    protected function registerIgnition(): void
    {
        $viteJsAutoRefresh = '';

        if (class_exists('Illuminate\Foundation\Vite')) {
            $vite = app(\Illuminate\Foundation\Vite::class);

            if (is_file($vite->hotFile())) {
                $viteJsAutoRefresh = $vite->__invoke([]);
            }
        }

        $ignitionConfig = new IgnitionConfig(
            hideSolutions: false,
            shouldDisplayException: true,
            inProductionEnvironment: $this->app->environment('production'),
            editor: config('ignition.editor'),
            remoteSitesPath: config('ignition.remote_sites_path'),
            localSitesPath: config('igni.local_sites_path'),
            theme: config('ignition.theme'),
            enableShareButton: config('ignition.enable_share_button'),
            customHtmlHead: $viteJsAutoRefresh,
            configPath: config('ignition.settings_file_path'),
            documentationLinkResolvers: [
                LaravelDocumentationLinkFinder::class
            ],
        );

        $ignitionConfig->loadSaveableOptions(
            FileConfigManager::fromIgnitionConfig($ignitionConfig)->load()
        );

        $this->provider = new IgnitionProvider($ignitionConfig, $this->app);

        $this->provider->register();
    }

    protected function registerRoutes(): void
    {
        $this->loadRoutesFrom(realpath(__DIR__.'/ignition-routes.php'));
    }
}
