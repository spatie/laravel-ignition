<?php

namespace Spatie\LaravelIgnition\Renderers;

use Spatie\Ignition\Ignition;
use Throwable;

class ErrorPageRenderer
{
    public function render(Throwable $throwable): void
    {
        app(Ignition::class)->renderException($throwable);
    }
}
