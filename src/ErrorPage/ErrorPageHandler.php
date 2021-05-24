<?php

namespace Spatie\LaravelIgnition\ErrorPage;

use Spatie\FlareClient\Flare;
use Spatie\Ignition\Ignition;
use Spatie\Ignition\Middleware\AddGitInformation;
use Spatie\Ignition\Middleware\SetNotifierName;
use Spatie\Ignition\SolutionProviders\BadMethodCallSolutionProvider;
use Spatie\Ignition\SolutionProviders\MergeConflictSolutionProvider;
use Spatie\Ignition\SolutionProviders\UndefinedPropertySolutionProvider;
use Spatie\LaravelIgnition\Context\LaravelContextDetector;
use Spatie\LaravelIgnition\Middleware\AddDumps;
use Spatie\LaravelIgnition\Middleware\AddEnvironmentInformation;
use Spatie\LaravelIgnition\Middleware\AddLogs;
use Spatie\LaravelIgnition\Middleware\AddQueries;
use Spatie\LaravelIgnition\SolutionProviders\DefaultDbNameSolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\IncorrectValetDbCredentialsSolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\InvalidRouteActionSolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\MissingAppKeySolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\MissingColumnSolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\MissingImportSolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\MissingLivewireComponentSolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\MissingMixManifestSolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\RunningLaravelDuskInProductionProvider;
use Spatie\LaravelIgnition\SolutionProviders\TableNotFoundSolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\UnknownValidationSolutionProvider;
use Spatie\LaravelIgnition\SolutionProviders\ViewNotFoundSolutionProvider;
use Throwable;

class ErrorPageHandler
{
    public function handle(Throwable $throwable) : void
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
            ->addSolutions($this->getSolutions())
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

    protected function getSolutions(): array
    {
        return [
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
        ];
    }
}
