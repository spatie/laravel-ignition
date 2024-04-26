<?php

namespace Spatie\LaravelIgnition\Support;

use Illuminate\Foundation\Configuration\Exceptions;
use Spatie\FlareClient\Flare;
use Throwable;

class LaravelFlare extends Flare
{
    public static function handles(Exceptions $exceptions): void
    {
        $exceptions->reportable(static function (Throwable $exception): Flare {
            return app(Flare::class)->report($exception);
        });
    }
}
