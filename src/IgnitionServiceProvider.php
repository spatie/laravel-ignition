<?php

namespace Spatie\LaravelIgnition;

use Exception;
use Illuminate\Contracts\Foundation\ExceptionRenderer;
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
use Spatie\LaravelIgnition\Commands\SolutionMakeCommand;
use Spatie\LaravelIgnition\Commands\SolutionProviderMakeCommand;
use Spatie\LaravelIgnition\Commands\TestCommand;
use Spatie\LaravelIgnition\Context\LaravelContextDetector;
use Spatie\LaravelIgnition\DumpRecorder\DumpRecorder;
use Spatie\LaravelIgnition\ErrorPage\IgnitionExceptionRenderer;
use Spatie\LaravelIgnition\ErrorPage\IgnitionWhoopsHandler;
use Spatie\Ignition\ErrorPage\Renderer;
use Spatie\LaravelIgnition\Exceptions\InvalidConfig;
use Spatie\LaravelIgnition\Http\Controllers\ExecuteSolutionController;
use Spatie\LaravelIgnition\Http\Controllers\HealthCheckController;
use Spatie\LaravelIgnition\Http\Middleware\IgnitionConfigValueEnabled;
use Spatie\LaravelIgnition\Http\Middleware\IgnitionEnabled;
use Spatie\LaravelIgnition\Logger\FlareLogHandler;
use Spatie\LaravelIgnition\LogRecorder\LogRecorder;
use Spatie\LaravelIgnition\Middleware\AddDumps;
use Spatie\LaravelIgnition\Middleware\AddEnvironmentInformation;
use Spatie\Ignition\Middleware\AddGitInformation;
use Spatie\LaravelIgnition\Middleware\AddLogs;
use Spatie\LaravelIgnition\Middleware\AddQueries;
use Spatie\Ignition\Middleware\AddSolutions;
use Spatie\Ignition\Middleware\SetNotifierName;
use Spatie\LaravelIgnition\QueryRecorder\QueryRecorder;
use Spatie\LaravelIgnition\SolutionProviders\MissingPackageSolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\UndefinedVariableSolutionProvider;
use Spatie\LaravelIgnition\Views\Engines\CompilerEngine;
use Spatie\LaravelIgnition\Views\Engines\PhpEngine;
use Throwable;

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
            ->registerRenderer()
            ->registerExceptionRenderer()
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

        $this->app
            ->get(Flare::class)
            ->censorRequestBodyFields(
                config('flare.reporting.censor_request_body_fields',
                    ['password'])
            );

        $this->registerBuiltInMiddleware();
    }

    protected function registerViewEngines()
    {
        if (!$this->hasCustomViewEnginesRegistered()) {
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
        });

        return $this;
    }

    protected function registerRenderer(): self
    {
        $this->app->bind(Renderer::class, fn() => new Renderer(__DIR__ . '/../resources/views/'));

        return $this;
    }

    protected function registerExceptionRenderer(): self
    {
        if (interface_exists(HandlerInterface::class)) {
            $this->app->bind(
                HandlerInterface::class,
                fn(Application $app) => $app->make(IgnitionWhoopsHandler::class)
            );
        }

        if (interface_exists(ExceptionRenderer::class)) {
            $this->app->bind(
                ExceptionRenderer::class,
                fn(Application $app) => $app->make(IgnitionExceptionRenderer::class)
            );
        }

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
            'flare.client',
            fn() => new Client(
                config('flare.key'),
                config('flare.secret'),
                config('flare.base_url', 'https://flareapp.io/api')
            )
        );

        $this->app->alias('flare.client', Client::class);

        $this->app->singleton(Flare::class, function () {
            $client = new Flare(
                $this->app->get('flare.client'),
                new LaravelContextDetector,
                $this->app,
            );
            $client->applicationPath(base_path());
            $client->stage(config('app.env'));

            return $client;
        });

        return $this;
    }

    protected function registerLogHandler(): self
    {
        $this->app->singleton('flare.logger', function ($app) {
            $handler = new FlareLogHandler($app->make(Flare::class));

            $logLevelString = config('logging.channels.flare.level', 'error');

            $logLevel = $this->getLogLevel($logLevelString);

            $handler->setMinimumReportLogLevel($logLevel);

            $logger = new Logger('Flare');
            $logger->pushHandler($handler);

            return $logger;
        });

        $this->app['log'] instanceof LogManager

            ? Log::extend('flare', fn($app) => $app['flare.logger'])
            : $this->bindLogListener();

        return $this;
    }

    protected function getLogLevel(string $logLevelString): int
    {
        $logLevel = Logger::getLevels()[strtoupper($logLevelString)] ?? null;

        if (!$logLevel) {
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
            ->map(fn(string $middlewareClass) => $this->app->make($middlewareClass));

        if (config('flare.reporting.collect_git_information')) {
            $middleware[] = (new AddGitInformation());
        }

        foreach ($middleware as $singleMiddleware) {
            $this->app->get(Flare::class)->registerMiddleware($singleMiddleware);
        }



        return $this;
    }

    protected function hasCustomViewEnginesRegistered(): bool
    {
        $resolver = $this->app->make('view.engine.resolver');

        if (!$resolver->resolve('php') instanceof LaravelPhpEngine) {
            return false;
        }

        if (!$resolver->resolve('blade') instanceof LaravelCompilerEngine) {
            return false;
        }

        return true;
    }

    protected function bindLogListener()
    {
        $this->app['log']->listen(function (MessageLogged $messageLogged) {
            if (!config('flare.key')) {
                return;
            }

            try {
                $this->app['flare.logger']->log(
                    $messageLogged->level,
                    $messageLogged->message,
                    $messageLogged->context
                );
            } catch (Exception $exception) {
                return;
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
