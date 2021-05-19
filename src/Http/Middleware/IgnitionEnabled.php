<?php

namespace Spatie\Ignition\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

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
