<?php

namespace Spatie\LaravelIgnition\FlareMiddleware;

use Composer\Composer;
use Spatie\FlareClient\Report;
use Spatie\FlareClient\FlareMiddleware\FlareMiddleware;

class AddNotifierName implements FlareMiddleware
{
    public const NOTIFIER_NAME = 'Laravel Client';

    public function handle(Report $report, $next)
    {
        $report->notifierName(static::NOTIFIER_NAME);

        return $next($report);
    }
}
