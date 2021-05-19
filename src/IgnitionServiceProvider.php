<?php

namespace Spatie\Ignition;

use Exception;
use Illuminate\Foundation\Application;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Log\LogManager;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\Engines\CompilerEngine as LaravelCompilerEngine;
use Illuminate\View\Engines\PhpEngine as LaravelPhpEngine;
use Livewire\CompilerEngineForIgnition;
use Monolog\Logger;
use Spatie\FlareClient\Api;
use Spatie\FlareClient\Flare;
use Spatie\FlareClient\Http\Client;
use Spatie\Ignition\Commands\SolutionMakeCommand;
use Spatie\Ignition\Commands\SolutionProviderMakeCommand;
use Spatie\Ignition\Commands\TestCommand;
use Spatie\Ignition\Context\LaravelContextDetector;
use Spatie\Ignition\DumpRecorder\DumpRecorder;
use Spatie\Ignition\ErrorPage\IgnitionWhoopsHandler;
use Spatie\Ignition\ErrorPage\Renderer;
use Spatie\Ignition\Exceptions\InvalidConfig;
use Spatie\Ignition\Http\Controllers\ExecuteSolutionController;
use Spatie\Ignition\Http\Controllers\HealthCheckController;
use Spatie\Ignition\Http\Controllers\ScriptController;
use Spatie\Ignition\Http\Controllers\StyleController;
use Spatie\Ignition\Http\Middleware\IgnitionConfigValueEnabled;
use Spatie\Ignition\Http\Middleware\IgnitionEnabled;
use Spatie\Ignition\Logger\FlareHandler;
use Spatie\Ignition\LogRecorder\LogRecorder;
use Spatie\Ignition\Middleware\AddDumps;
use Spatie\Ignition\Middleware\AddEnvironmentInformation;
use Spatie\Ignition\Middleware\AddGitInformation;
use Spatie\Ignition\Middleware\AddLogs;
use Spatie\Ignition\Middleware\AddQueries;
use Spatie\Ignition\Middleware\AddSolutions;
use Spatie\Ignition\Middleware\SetNotifierName;
use Spatie\Ignition\QueryRecorder\QueryRecorder;
use Spatie\Ignition\SolutionProviders\BadMethodCallSolutionProvider;
use Spatie\Ignition\SolutionProviders\DefaultDbNameSolutionProvider;
use Spatie\Ignition\SolutionProviders\IncorrectValetDbCredentialsSolutionProvider;
use Spatie\Ignition\SolutionProviders\InvalidRouteActionSolutionProvider;
use Spatie\Ignition\SolutionProviders\MergeConflictSolutionProvider;
use Spatie\Ignition\SolutionProviders\MissingAppKeySolutionProvider;
use Spatie\Ignition\SolutionProviders\MissingColumnSolutionProvider;
use Spatie\Ignition\SolutionProviders\MissingImportSolutionProvider;
use Spatie\Ignition\SolutionProviders\MissingLivewireComponentSolutionProvider;
use Spatie\Ignition\SolutionProviders\MissingMixManifestSolutionProvider;
use Spatie\Ignition\SolutionProviders\MissingPackageSolutionProvider;
use Spatie\Ignition\SolutionProviders\RunningLaravelDuskInProductionProvider;
use Spatie\Ignition\SolutionProviders\SolutionProviderRepository;
use Spatie\Ignition\SolutionProviders\TableNotFoundSolutionProvider;
use Spatie\Ignition\SolutionProviders\UndefinedPropertySolutionProvider;
use Spatie\Ignition\SolutionProviders\UndefinedVariableSolutionProvider;
use Spatie\Ignition\SolutionProviders\UnknownValidationSolutionProvider;
use Spatie\Ignition\SolutionProviders\ViewNotFoundSolutionProvider;
use Spatie\Ignition\Views\Engines\CompilerEngine;
use Spatie\Ignition\Views\Engines\PhpEngine;
use Spatie\IgnitionContracts\SolutionProviderRepository as SolutionProviderRepositoryContract;
use Throwable;
use Whoops\Handler\HandlerInterface;

class IgnitionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/flare.php' => config_path('flare.php'),
            ], 'flare-config');

            $this->publishes([
                __DIR__ . '/../config/ignition.php' => config_path('ignition.php'),
            ], 'ignition-config');

            if (isset($_SERVER['argv']) && ['artisan', 'tinker'] === $_SERVER['argv']) {
                Api::sendReportsInBatches(false);
            }
        }

        $this
            ->registerViewEngines()
            ->registerHousekeepingRoutes()
            ->registerLogHandler()
            ->registerCommands();

        if ($this->app->bound('queue')) {
            $this->setupQueue($this->app->get('queue'));
        }

        if (config('flare.reporting.report_logs')) {
            $this->app->make(LogRecorder::class)->register();
        }

        if (config('flare.reporting.report_queries')) {
            $this->app->make(QueryRecorder::class)->register();
        }

        $this->app->make(DumpRecorder::class)->register();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/flare.php', 'flare');
        $this->mergeConfigFrom(__DIR__ . '/../config/ignition.php', 'ignition');

        $this
            ->registerSolutionProviderRepository()
            ->registerExceptionRenderer()
            ->registerWhoopsHandler()
            ->registerIgnitionConfig()
            ->registerFlare()
            ->registerDumpCollector();

        if (config('flare.reporting.report_logs')) {
            $this->registerLogRecorder();
        }

        if (config('flare.reporting.report_queries')) {
            $this->registerQueryRecorder();
        }

        if (config('flare.reporting.anonymize_ips')) {
            $this->app->get(Flare::class)->anonymizeIp();
        }

        $this->app->get(Flare::class)->censorRequestBodyFields(config('flare.reporting.censor_request_body_fields', ['password']));

        $this->registerBuiltInMiddleware();
    }

    protected function registerViewEngines()
    {
        if (! $this->hasCustomViewEnginesRegistered()) {
            return $this;
        }

        $this->app->make('view.engine.resolver')->register('php', function () {
            return new PhpEngine($this->app['files']);
        });

        $this->app->make('view.engine.resolver')->register('blade', function () {
            if (class_exists(CompilerEngineForIgnition::class)) {
                return new CompilerEngineForIgnition($this->app['blade.compiler']);
            }

            return new CompilerEngine($this->app['blade.compiler']);
        });

        return $this;
    }

    protected function registerHousekeepingRoutes()
    {
        if ($this->app->runningInConsole()) {
            return $this;
        }

        Route::group([
            'as' => 'ignition.',
            'prefix' => config('ignition.housekeeping_endpoint_prefix', '_ignition'),
            'middleware' => [IgnitionEnabled::class],
        ], function () {
            Route::get('health-check', HealthCheckController::class)->name('healthCheck');

            Route::post('execute-solution', ExecuteSolutionController::class)
                ->middleware(IgnitionConfigValueEnabled::class . ':enableRunnableSolutions')
                ->name('executeSolution');

            Route::get('scripts/{script}', ScriptController::class)->name('scripts');
            Route::get('styles/{style}', StyleController::class)->name('styles');
        });

        return $this;
    }

    protected function registerSolutionProviderRepository(): self
    {
        $this->app->singleton(SolutionProviderRepositoryContract::class, function () {
            $defaultSolutions = $this->getDefaultSolutions();

            return new SolutionProviderRepository($defaultSolutions);
        });

        return $this;
    }

    protected function registerExceptionRenderer(): self
    {
        $this->app->bind(Renderer::class, fn () => new Renderer(__DIR__ . '/../resources/views/'));

        return $this;
    }

    protected function registerWhoopsHandler(): self
    {
        $this->app->bind(HandlerInterface::class, fn (Application $app) => $app->make(IgnitionWhoopsHandler::class));

        return $this;
    }

    protected function registerIgnitionConfig(): self
    {
        $this->app->singleton(IgnitionConfig::class, function () {
            $options = [];

            try {
                if ($configPath = $this->getConfigFileLocation()) {
                    $options = require $configPath;
                }
            } catch (Throwable $e) {
                // possible open_basedir restriction
            }

            return new IgnitionConfig($options);
        });

        return $this;
    }

    protected function registerFlare(): self
    {
        $this->app->singleton(
            'flare.http',
            fn () => new Client(
                config('flare.key'),
                config('flare.secret'),
                config('flare.base_url', 'https://flareapp.io/api')
            )
        );

        $this->app->alias('flare.http', Client::class);

        $this->app->singleton(Flare::class, function () {
            $client = new Flare($this->app->get('flare.http'), new LaravelContextDetector, $this->app);
            $client->applicationPath(base_path());
            $client->stage(config('app.env'));

            return $client;
        });

        return $this;
    }

    protected function registerLogHandler(): self
    {
        $this->app->singleton('flare.logger', function ($app) {
            $handler = new FlareHandler($app->make(Flare::class));

            $logLevelString = config('logging.channels.flare.level', 'error');

            $logLevel = $this->getLogLevel($logLevelString);

            $handler->setMinimumReportLogLevel($logLevel);

            $logger = new Logger('Flare');
            $logger->pushHandler($handler);

            return $logger;
        });

        $this->app['log'] instanceof LogManager
            ?  Log::extend('flare', fn ($app) => $app['flare.logger'])
            : $this->bindLogListener();

        return $this;
    }

    protected function getLogLevel(string $logLevelString): int
    {
        $logLevel = Logger::getLevels()[strtoupper($logLevelString)] ?? null;

        if (! $logLevel) {
            throw InvalidConfig::invalidLogLevel($logLevelString);
        }

        return $logLevel;
    }

    protected function registerLogRecorder(): self
    {
        $this->app->singleton(LogRecorder::class, function (Application $app): LogRecorder {
            return new LogRecorder(
                $app,
                $app->get('config')->get('flare.reporting.maximum_number_of_collected_logs')
            );
        });

        return $this;
    }

    protected function registerDumpCollector(): self
    {
        $dumpCollector = $this->app->make(DumpRecorder::class);

        $this->app->singleton(DumpRecorder::class);

        $this->app->instance(DumpRecorder::class, $dumpCollector);

        return $this;
    }

    protected function registerCommands(): self
    {
        $this->app->bind('command.flare:test', TestCommand::class);
        $this->app->bind('command.make:solution', SolutionMakeCommand::class);
        $this->app->bind('command.make:solution-provider', SolutionProviderMakeCommand::class);

        if ($this->app['config']->get('flare.key')) {
            $this->commands(['command.flare:test']);
        }

        if ($this->app['config']->get('ignition.register_commands', false)) {
            $this->commands(['command.make:solution']);
            $this->commands(['command.make:solution-provider']);
        }

        return $this;
    }

    protected function registerQueryRecorder(): self
    {
        $this->app->singleton(QueryRecorder::class, function (Application $app): QueryRecorder {
            return new QueryRecorder(
                $app,
                $app->get('config')->get('flare.reporting.report_query_bindings'),
                $app->get('config')->get('flare.reporting.maximum_number_of_collected_queries')
            );
        });

        return $this;
    }

    protected function registerBuiltInMiddleware(): self
    {
        $middlewares = [
            SetNotifierName::class,
            AddEnvironmentInformation::class,
        ];

        if (config('flare.reporting.report_logs')) {
            $middlewares[] = AddLogs::class;
        }

        $middlewares[] = AddDumps::class;

        if (config('flare.reporting.report_queries')) {
            $middlewares[] = AddQueries::class;
        }

        $middlewares[] = AddSolutions::class;

        $middleware = collect($middlewares)
            ->map(function (string $middlewareClass) {
                return $this->app->make($middlewareClass);
            });

        if (config('flare.reporting.collect_git_information')) {
            $middleware[] = (new AddGitInformation());
        }

        foreach ($middleware as $singleMiddleware) {
            $this->app->get(Flare::class)->registerMiddleware($singleMiddleware);
        }

        return $this;
    }

    protected function getDefaultSolutions(): array
    {
        return [
            IncorrectValetDbCredentialsSolutionProvider::class,
            MissingAppKeySolutionProvider::class,
            DefaultDbNameSolutionProvider::class,
            BadMethodCallSolutionProvider::class,
            TableNotFoundSolutionProvider::class,
            MissingImportSolutionProvider::class,
            MissingPackageSolutionProvider::class,
            InvalidRouteActionSolutionProvider::class,
            ViewNotFoundSolutionProvider::class,
            UndefinedVariableSolutionProvider::class,
            MergeConflictSolutionProvider::class,
            RunningLaravelDuskInProductionProvider::class,
            MissingColumnSolutionProvider::class,
            UnknownValidationSolutionProvider::class,
            UndefinedPropertySolutionProvider::class,
            MissingMixManifestSolutionProvider::class,
            MissingLivewireComponentSolutionProvider::class,
        ];
    }

    protected function hasCustomViewEnginesRegistered(): bool
    {
        $resolver = $this->app->make('view.engine.resolver');

        if (! $resolver->resolve('php') instanceof LaravelPhpEngine) {
            return false;
        }

        if (! $resolver->resolve('blade') instanceof LaravelCompilerEngine) {
            return false;
        }

        return true;
    }

    protected function bindLogListener()
    {
        $this->app['log']->listen(function (MessageLogged $messageLogged) {
            if (config('flare.key')) {
                try {
                    $this->app['flare.logger']->log(
                        $messageLogged->level,
                        $messageLogged->message,
                        $messageLogged->context
                    );
                } catch (Exception $exception) {
                    return;
                }
            }
        });
    }

    protected function getConfigFileLocation(): ?string
    {
        $configFullPath = base_path() . DIRECTORY_SEPARATOR . '.ignition';

        if (file_exists($configFullPath)) {
            return $configFullPath;
        }

        $configFullPath = Arr::get($_SERVER, 'HOME', '') . DIRECTORY_SEPARATOR . '.ignition';

        if (file_exists($configFullPath)) {
            return $configFullPath;
        }

        return null;
    }

    protected function setupQueue(QueueManager $queue)
    {
        $queue->looping(function () {
            $this->app->get(Flare::class)->reset();

            if (config('flare.reporting.report_logs')) {
                $this->app->make(LogRecorder::class)->reset();
            }

            if (config('flare.reporting.report_queries')) {
                $this->app->make(QueryRecorder::class)->reset();
            }

            $this->app->make(DumpRecorder::class)->reset();
        });
    }
}
