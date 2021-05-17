<?php

namespace Spatie\Ignition\Http\Controllers;

use Spatie\Ignition\Ignition;
use Illuminate\Http\Request;

class ScriptController
{
    public function __invoke(Request $request)
    {
        return response(
            file_get_contents(
                Ignition::scripts()[$request->script]
            ),
            200,
            ['Content-Type' => 'application/javascript']
        );
    }
}
