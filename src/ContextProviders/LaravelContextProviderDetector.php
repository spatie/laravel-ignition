<?php

namespace Spatie\LaravelIgnition\ContextProviders;

use Illuminate\Http\Request;
use Spatie\FlareClient\Context\ContextProvider;
use Spatie\FlareClient\Context\ContextProviderDetector;

class LaravelContextProviderDetector implements ContextProviderDetector
{
    public function detectCurrentContext(): ContextProvider
    {
        return app()->runningInConsole()
            ? new LaravelConsoleContextProvider($_SERVER['argv'] ?? [])
            : new LaravelRequestContextProvider(app(Request::class));
    }
}
