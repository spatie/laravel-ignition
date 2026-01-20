<?php

namespace Spatie\LaravelIgnition\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Ignition\Config\IgnitionConfig;
use Spatie\Ignition\Ignition;
use Spatie\LaravelIgnition\Http\Requests\UpdateConfigRequest;

class UpdateConfigController
{
    public function __invoke(Request $request, Ignition $ignition)
    {
        $ignition->getUpdateConfigAction()->execute($request);

        return 'ok';
    }
}
