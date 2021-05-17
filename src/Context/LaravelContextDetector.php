<?php

namespace Spatie\Ignition\Context;

use Spatie\FlareClient\Context\ContextDetectorInterface;
use Spatie\FlareClient\Context\ContextInterface;
use Illuminate\Http\Request;

class LaravelContextDetector implements ContextDetectorInterface
{
    public function detectCurrentContext(): ContextInterface
    {
        if (app()->runningInConsole()) {
            return new LaravelConsoleContext($_SERVER['argv'] ?? []);
        }

        return new LaravelRequestContext(app(Request::class));
    }
}
