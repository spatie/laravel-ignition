<?php

namespace Spatie\Ignition\Context;

use Illuminate\Http\Request;
use Spatie\FlareClient\Context\ConsoleContext;
use Spatie\FlareClient\Context\ContextDetectorInterface;
use Spatie\FlareClient\Context\ContextInterface;

class LaravelContextDetector implements ContextDetectorInterface
{
    public function detectCurrentContext(): ContextInterface
    {
        return app()->runningInConsole()
            ? new LaravelConsoleContext($_SERVER['argv'] ?? [])
            : new LaravelRequestContext(app(Request::class));
    }
}
