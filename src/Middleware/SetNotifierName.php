<?php

namespace Spatie\Ignition\Middleware;

use Spatie\FlareClient\Report;

class SetNotifierName
{
    const NOTIFIER_NAME = 'Laravel Client';

    public function handle(Report $report, $next)
    {
        $report->notifierName(static::NOTIFIER_NAME);

        return $next($report);
    }
}
