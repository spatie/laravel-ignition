<?php

namespace Spatie\LaravelIgnition\FlareMiddleware;

use Illuminate\Database\QueryException;
use Spatie\FlareClient\FlareMiddleware\FlareMiddleware;
use Spatie\FlareClient\Report;

class AddExceptionInformation implements FlareMiddleware
{
    public function handle(Report $report, $next)
    {
        $throwable = $report->getThrowable();

        if (! $throwable instanceof QueryException) {
            return $next($report);
        }

        $report->group('exception', [
            'raw_sql' => $throwable->getSql(),
        ]);

        return $next($report);
    }
}
