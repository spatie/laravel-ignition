<?php

namespace Spatie\LaravelIgnition\Renderers;

use Spatie\FlareClient\Flare;
use Spatie\Ignition\Ignition;
use Spatie\Ignition\Middleware\AddGitInformation;
use Spatie\Ignition\Middleware\SetNotifierName;
use Spatie\Ignition\SolutionProviders\BadMethodCallSolutionProvider;
use Spatie\Ignition\SolutionProviders\MergeConflictSolutionProvider;
use Spatie\Ignition\SolutionProviders\UndefinedPropertySolutionProvider;
use Spatie\LaravelIgnition\Context\LaravelContextDetector;
use Spatie\LaravelIgnition\FlareMiddleware\AddDumps;
use Spatie\LaravelIgnition\FlareMiddleware\AddEnvironmentInformation;
use Spatie\LaravelIgnition\FlareMiddleware\AddLogs;
use Spatie\LaravelIgnition\FlareMiddleware\AddQueries;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\DefaultDbNameSolutionProvider;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\IncorrectValetDbCredentialsSolutionProvider;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\InvalidRouteActionSolutionProvider;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\MissingAppKeySolutionProvider;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\MissingColumnSolutionProvider;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\MissingImportSolutionProvider;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\MissingLivewireComponentSolutionProvider;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\MissingMixManifestSolutionProvider;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\RunningLaravelDuskInProductionProvider;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\TableNotFoundSolutionProvider;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\UnknownValidationSolutionProvider;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\ViewNotFoundSolutionProvider;
use Throwable;

class ErrorPageRenderer
{
    public function render(Throwable $throwable): void
    {
        /** @var Ignition $ignition */
        $ignition = app(Ignition::class);

        $ignition
            ->configureFlare(function (Flare $flare) {
                $flare
                    ->setApiToken(config('flare.key'))
                    ->setApiSecret(config('flare.secret'))
                    ->setBaseUrl(config('flare.base_url', 'https://flareapp.io/api'))
                    ->setContextDectector(new LaravelContextDetector)
                    ->setStage(config('app.env'))
                    ->censorRequestBodyFields(config(
                        'flare.reporting.censor_request_body_fields',
                        ['password']
                    ));

                if (config('flare.reporting.anonymize_ips')) {
                    $flare->anonymizeIp();
                }
            })
            ->applicationPath(base_path())
            ->addSolutionProviders($this->getSolutionProviders())
            ->registerMiddleware($this->getMiddlewares())
            ->renderException($throwable);
    }

    protected function getMiddlewares(): array
    {
        $middlewares = [
            SetNotifierName::class,
            AddEnvironmentInformation::class,
            AddDumps::class,
        ];

        if (config('flare.reporting.report_logs')) {
            $middlewares[] = AddLogs::class;
        }

        if (config('flare.reporting.report_queries')) {
            $middlewares[] = AddQueries::class;
        }

        if (config('flare.reporting.collect_git_information')) {
            $middlewares[] = AddGitInformation::class;
        }

        return collect($middlewares)
            ->map(fn (string $middlewareClass) => $this->app->make($middlewareClass))
            ->toArray();
    }

    protected function getSolutionProviders(): array
    {
        return collect([
            IncorrectValetDbCredentialsSolutionProvider::class,
            MissingAppKeySolutionProvider::class,
            DefaultDbNameSolutionProvider::class,
            BadMethodCallSolutionProvider::class,
            TableNotFoundSolutionProvider::class,
            MissingImportSolutionProvider::class,
            InvalidRouteActionSolutionProvider::class,
            ViewNotFoundSolutionProvider::class,
            MergeConflictSolutionProvider::class,
            RunningLaravelDuskInProductionProvider::class,
            MissingColumnSolutionProvider::class,
            UnknownValidationSolutionProvider::class,
            UndefinedPropertySolutionProvider::class,
            MissingMixManifestSolutionProvider::class,
            MissingLivewireComponentSolutionProvider::class,
        ])
            ->reject(fn(string $class) => in_array($class, config('ignition.ignored_solution_providers')))
            ->toArray();
    }
}
