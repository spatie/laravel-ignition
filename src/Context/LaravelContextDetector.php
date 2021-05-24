<?php

namespace Spatie\LaravelIgnition\Context;

use Illuminate\Http\Request;
use Spatie\FlareClient\Context\ContextDetectorInterface;
use Spatie\FlareClient\Context\ContextInterface;
use Spatie\FlareClient\Context\ContextProvider;
use Spatie\FlareClient\Context\ContextProviderDetector;

class LaravelContextDetector implements ContextProviderDetector
{
    public function detectCurrentContext(): ContextProvider
    {
        return app()->runningInConsole()
            ? new LaravelConsoleContext($_SERVER['argv'] ?? [])
            : new LaravelRequestContext(app(Request::class));
    }
}
