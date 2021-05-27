<?php

namespace Spatie\LaravelIgnition\Http\Middleware;

use Closure;

class IgnitionEnabled
{
    public function handle($request, Closure $next)
    {

        if (! $this->ignitionEnabled()) {

            abort(404);
        }

        return $next($request);
    }

    protected function ignitionEnabled(): bool
    {
        return config('app.debug');
    }
}
