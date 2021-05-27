<?php

namespace Spatie\LaravelIgnition;

use Illuminate\Contracts\Foundation\ExceptionRenderer;
use Illuminate\Foundation\Application;
use Illuminate\Queue\QueueManager;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Engines\CompilerEngine as LaravelCompilerEngine;
use Illuminate\View\Engines\PhpEngine as LaravelPhpEngine;
use Livewire\CompilerEngineForIgnition;
use Monolog\Logger;
use Spatie\FlareClient\Flare;
use Spatie\Ignition\Config\IgnitionConfig;
use Spatie\Ignition\Ignition;
use Spatie\IgnitionContracts\SolutionProviderRepository as SolutionProviderRepositoryContract;
use Spatie\LaravelIgnition\Commands\SolutionMakeCommand;
use Spatie\LaravelIgnition\Commands\SolutionProviderMakeCommand;
use Spatie\LaravelIgnition\Commands\TestCommand;
use Spatie\LaravelIgnition\Exceptions\InvalidConfig;
use Spatie\LaravelIgnition\FlareMiddleware\AddLogs;
use Spatie\LaravelIgnition\FlareMiddleware\AddQueries;
use Spatie\LaravelIgnition\Http\Controllers\ExecuteSolutionController;
use Spatie\LaravelIgnition\Http\Controllers\HealthCheckController;
use Spatie\LaravelIgnition\Http\Middleware\RunnableSolutionsEnabled;
use Spatie\LaravelIgnition\Recorders\DumpRecorder\DumpRecorder;
use Spatie\LaravelIgnition\Recorders\LogRecorder\LogRecorder;
use Spatie\LaravelIgnition\Recorders\QueryRecorder\QueryRecorder;
use Spatie\LaravelIgnition\Renderers\IgnitionExceptionRenderer;
use Spatie\LaravelIgnition\Renderers\IgnitionWhoopsHandler;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\SolutionProviderRepository;
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

    public function packageRegistered(): void
    {
        $this
            ->registerFlare()
            ->registerIgnition()
            ->registerRenderer()
            ->registerRecorders();
    }

    public function packageBooted(): void
    {
        $this
            ->configureTinker()
            ->registerViewEngines()
            ->registerRoutes()
            ->registerLogHandler()
            ->startRecorders()
            ->configureQueue();
    }

    protected function registerRenderer(): self
    {
        if (interface_exists(HandlerInterface::class)) {
            ray('register renderer');

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

    protected function registerFlare(): self
    {
        $this->app->singleton(Flare::class, function () {
            return Flare::make()
                ->setApiToken(config('flare.key') ?? '')
                ->setBaseUrl(config('flare.base_url', 'https://flareapp.io/api'))
                ->setStage(config('app.env'))
                ->registerMiddleware($this->getFlareMiddleware());
        });

        return $this;
    }

    protected function registerIgnition(): self
    {
        $ignitionConfig = IgnitionConfig::loadFromConfigFile()->merge(config('ignition'));

        $solutionProviders = $this->getSolutionProviders();
        $solutionProviderRepository = new SolutionProviderRepository($solutionProviders);

        $this->app->singleton(IgnitionConfig::class, fn () => $ignitionConfig);

        $this->app->singleton(SolutionProviderRepositoryContract::class, fn () => $solutionProviderRepository);

        $this->app->singleton(Ignition::class, fn () => (new Ignition()));

        return $this;
    }

    protected function registerRecorders(): self
    {
        $dumpCollector = $this->app->make(DumpRecorder::class);
        $this->app->singleton(DumpRecorder::class);
        $this->app->instance(DumpRecorder::class, $dumpCollector);

        if (config('flare.reporting.report_logs')) {
            $this->app->singleton(LogRecorder::class, function (Application $app): LogRecorder {
                return new LogRecorder(
                    $app,
                    config()->get('flare.flare_middleware' . AddLogs::class . 'maximum_number_of_collected_logs')
                );
            });
        }

        if (config('flare.middleware.' . AddQueries::class)) {
            $this->app->singleton(
                QueryRecorder::class,
                function (Application $app): QueryRecorder {
                    return new QueryRecorder(
                        $app,
                        config()->get('flare.middleware.' . AddQueries::class . '.report_query_bindings'),
                        config()->get('flare.middleware.' . AddQueries::class . '.maximum_number_of_collected_queries')
                    );
                }
            );
        }

        return $this;
    }

    public function configureTinker(): self
    {
        if (! $this->app->runningInConsole()) {
            if (isset($_SERVER['argv']) && ['artisan', 'tinker'] === $_SERVER['argv']) {
                app(Flare::class)->sendReportsImmediately();
            }
        }

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
            'middleware' => [RunnableSolutionsEnabled::class],
        ], function () {
            Route::get('health-check', HealthCheckController::class)->name('healthCheck');

            Route::post('execute-solution', ExecuteSolutionController::class)
                ->name('executeSolution');
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

            return tap(
                new Logger('Flare'),
                fn (Logger $logger) => $logger->pushHandler($handler)
            );
        });

        Log::extend('flare', fn ($app) => $app['flare.logger']);

        return $this;
    }

    protected function startRecorders(): self
    {
        if (config('flare.reporting.report_logs')) {
            $this->app->make(LogRecorder::class)->start();
        }

        if (config('flare.reporting.report_queries')) {
            $this->app->make(QueryRecorder::class)->start();
        }

        $this->app->make(DumpRecorder::class)->start();

        return $this;
    }

    protected function configureQueue(): self
    {
        if (! $this->app->bound('queue')) {
            return $this;
        }

        /** @var QueueManager $queue */
        $queue = $this->app->get('queue');

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

    protected function getLogLevel(string $logLevelString): int
    {
        $logLevel = Logger::getLevels()[strtoupper($logLevelString)] ?? null;

        if (! $logLevel) {
            throw InvalidConfig::invalidLogLevel($logLevelString);
        }

        return $logLevel;
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

    protected function getFlareMiddleware(): array
    {
        return collect(config('flare.flare_middleware'))
            ->map(function ($value, $key) {
                if (is_string($key)) {
                    $middlewareClass = $key;
                    $parameters = $value ?? [];
                } else {
                    $middlewareClass = $value;
                    $parameters = [];
                }

                return new $middlewareClass(...array_values($parameters));
            })
            ->values()
            ->toArray();
    }

    protected function getSolutionProviders(): array
    {
        return collect(config('ignition.solution_providers'))
            ->reject(
                fn (string $class) => in_array($class, config('ignition.ignored_solution_providers'))
            )
            ->toArray();
    }
}
