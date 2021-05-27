<?php

namespace Spatie\LaravelIgnition\Renderers;

use Spatie\FlareClient\Flare;
use Spatie\Ignition\Config\IgnitionConfig;
use Spatie\Ignition\Ignition;
use Spatie\IgnitionContracts\SolutionProviderRepository;
use Spatie\LaravelIgnition\ContextProviders\LaravelContextProviderDetector;
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
