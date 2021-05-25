<?php

namespace Spatie\LaravelIgnition\Renderers;

use Spatie\FlareClient\Flare;
use Spatie\FlareClient\FlareMiddleware\AddGitInformation;
use Spatie\FlareClient\FlareMiddleware\SetNotifierName;
use Spatie\Ignition\Ignition;
use Spatie\LaravelIgnition\ContextProviders\LaravelContextProviderDetector;
use Spatie\LaravelIgnition\FlareMiddleware\AddDumps;
use Spatie\LaravelIgnition\FlareMiddleware\AddEnvironmentInformation;
use Spatie\LaravelIgnition\FlareMiddleware\AddLogs;
use Spatie\LaravelIgnition\FlareMiddleware\AddQueries;
use Throwable;

class ErrorPageRenderer
{
    public function render(Throwable $throwable): void
    {
        /** @var Ignition $ignition */
        $ignition = app(Ignition::class);

        $ignition
            ->setFlare(app(Flare::class)) // consider adding this and move configuration of flare to service provider
            ->configureFlare(function (Flare $flare) {
                $flare
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
            })
            ->setContextProviderDetector(new LaravelContextProviderDetector())
            ->applicationPath(base_path())
            ->addSolutionProviders($this->getSolutionProviders())
            ->registerMiddleware($this->getMiddlewares()) // move to flare registration in service provider
            ->handleException($throwable);
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
            ->map(fn (string $middlewareClass) => app($middlewareClass))
            ->toArray();
    }

    protected function getSolutionProviders(): array
    {
        return collect(config('ignition.ignored_solution_providers'))
            ->reject(
                fn (string $class) => in_array($class, config('ignition.ignored_solution_providers'))
            )
            ->toArray();
    }
}
