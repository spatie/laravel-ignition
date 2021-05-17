<?php

namespace Spatie\Ignition\Http\Middleware;

use Closure;
use Spatie\Ignition\IgnitionConfig;
use Illuminate\Http\Request;

class IgnitionConfigValueEnabled
{
    /** @var \Spatie\Ignition\IgnitionConfig */
    protected $ignitionConfig;

    public function __construct(IgnitionConfig $ignitionConfig)
    {
        $this->ignitionConfig = $ignitionConfig;
    }

    public function handle(Request $request, Closure $next, string $value)
    {
        if (! $this->ignitionConfig->toArray()[$value]) {
            abort(404);
        }

        return $next($request);
    }
}
