<?php

namespace Spatie\LaravelIgnition;

use Exception;
use Illuminate\Contracts\Foundation\ExceptionRenderer;
use Illuminate\Foundation\Application;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Log\LogManager;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Engines\CompilerEngine as LaravelCompilerEngine;
use Illuminate\View\Engines\PhpEngine as LaravelPhpEngine;
use Livewire\CompilerEngineForIgnition;
use Monolog\Logger;
use Spatie\FlareClient\Api;
use Spatie\FlareClient\Flare;
use Spatie\Ignition\Ignition;
use Spatie\LaravelIgnition\Commands\SolutionMakeCommand;
use Spatie\LaravelIgnition\Commands\SolutionProviderMakeCommand;
use Spatie\LaravelIgnition\Commands\TestCommand;
use Spatie\LaravelIgnition\Exceptions\InvalidConfig;
use Spatie\LaravelIgnition\Http\Controllers\ExecuteSolutionController;
use Spatie\LaravelIgnition\Http\Controllers\HealthCheckController;
use Spatie\LaravelIgnition\Http\Middleware\IgnitionConfigValueEnabled;
use Spatie\LaravelIgnition\Http\Middleware\IgnitionEnabled;
use Spatie\LaravelIgnition\Recorders\DumpRecorder\DumpRecorder;
use Spatie\LaravelIgnition\Recorders\LogRecorder\LogRecorder;
use Spatie\LaravelIgnition\Recorders\QueryRecorder\QueryRecorder;
use Spatie\LaravelIgnition\Renderers\IgnitionExceptionRenderer;
use Spatie\LaravelIgnition\Renderers\IgnitionWhoopsHandler;
use Spatie\LaravelIgnition\Support\FlareLogHandler;
use Spatie\LaravelIgnition\Views\Engines\CompilerEngine;
use Spatie\LaravelIgnition\Views\Engines\PhpEngine;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Whoops\Handler\HandlerInterface;

class IgnitionServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-ignition')
            ->hasConfigFile(['flare', 'ignition']);

        if ($this->app['config']->get('flare.key')) {
            $package->hasCommands([
                TestCommand::class,
            ]);
        }

        if ($this->app['config']->get('ignition.register_commands')) {
            $package->hasCommands([
                SolutionMakeCommand::class,
                SolutionProviderMakeCommand::class,
            ]);
        }
    }

    public function packageBooted()
    {
        if ($this->app->runningInConsole()) {
            if (isset($_SERVER['argv']) && ['artisan', 'tinker'] === $_SERVER['argv']) {
                Api::sendReportsInBatches(false); //TODO: add method on flare for this
            }
        }

        $this
            ->registerViewEngines()
            ->registerRoutes()
            ->registerLogHandler();

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

    public function packageRegistered()
    {
        $this
            ->registerFlare()
            ->registerIgnition()
            ->registerRenderer()
            ->registerDumpCollector();

        if (config('flare.reporting.report_logs')) {
            $this->registerLogRecorder();
        }

        if (config('flare.reporting.report_queries')) {
            $this->registerQueryRecorder();
        }
    }

    protected function registerFlare(): self
    {
        $this->app->singleton(Flare::class, function() {
            $flare = Flare::make()
                ->setApiToken(config('flare.key') ?? '')
                ->setBaseUrl(config('flare.base_url', 'https://flareapp.io/api'))
                ->setStage(config('app.env'))
                ->censorRequestBodyFields(config(
                    'flare.reporting.censor_request_body_fields',
                    ['password']
                ));

            if (config('flare.reporting.anonymize_ips')) {
                $flare->anonymizeIp();
            }

            return $flare;
        });

        return $this;
    }

    protected function registerIgnition(): self
    {
        $this->app->singleton(Ignition::class, fn () => new Ignition());

        return $this;
    }

    protected function registerViewEngines(): self
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

    protected function registerRoutes(): self
    {
        if ($this->app->runningInConsole()) {
            return $this;
        }

        Route::group([
            'as' => 'ignition.',
            'prefix' => config('ignition.housekeeping_endpoint_prefix'),
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
        if (interface_exists(HandlerInterface::class)) {
            $this->app->bind(
                HandlerInterface::class,
                fn (Application $app) => $app->make(IgnitionWhoopsHandler::class)
            );
        }

        if (interface_exists(ExceptionRenderer::class)) {
            $this->app->bind(
                ExceptionRenderer::class,
                fn (Application $app) => $app->make(IgnitionExceptionRenderer::class)
            );
        }

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

            ? Log::extend('flare', fn ($app) => $app['flare.logger'])
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

    protected function registerQueryRecorder(): self
    {
        $this->app->singleton(
            QueryRecorder::class,
            function (Application $app): QueryRecorder {
                return new QueryRecorder(
                    $app,
                    $app->get('config')->get('flare.reporting.report_query_bindings'),
                    $app->get('config')->get('flare.reporting.maximum_number_of_collected_queries')
                );
            }
        );

        return $this;
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
            if (! config('flare.key')) {
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

    protected function setupQueue(QueueManager $queue): self
    {
        $queue->looping(function () {
            $this->app->get(Ignition::class)->reset();

            if (config('flare.reporting.report_logs')) {
                $this->app->make(LogRecorder::class)->reset();
            }

            if (config('flare.reporting.report_queries')) {
                $this->app->make(QueryRecorder::class)->reset();
            }

            $this->app->make(DumpRecorder::class)->reset();
        });

        return $this;
    }
}
