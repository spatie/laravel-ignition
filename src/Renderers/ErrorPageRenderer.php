<?php

namespace Spatie\LaravelIgnition\Renderers;

use Spatie\FlareClient\Flare;
use Spatie\FlareClient\FlareMiddleware\AddGitInformation;
use Spatie\FlareClient\FlareMiddleware\AddNotifierName;
use Spatie\Ignition\Config\IgnitionConfig;
use Spatie\Ignition\Ignition;
use Spatie\IgnitionContracts\SolutionProviderRepository;
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
            ->setFlare(app(Flare::class))
            ->setConfig(app(IgnitionConfig::class))
            ->setSolutionProviderRepository(app(SolutionProviderRepository::class))
            ->setContextProviderDetector(new LaravelContextProviderDetector())
            ->applicationPath(base_path())
            ->handleException($throwable);
    }
}
