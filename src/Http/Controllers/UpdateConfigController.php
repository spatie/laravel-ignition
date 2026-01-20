<?php

namespace Spatie\LaravelIgnition\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Ignition\Ignition;

class UpdateConfigController
{
    public function __invoke(Request $request, Ignition $ignition)
    {
        $ignition->getUpdateConfigAction()->execute($request);

        return 'ok';
    }
}
