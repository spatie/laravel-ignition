<?php

namespace Spatie\Ignition\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Ignition\Ignition;

class StyleController
{
    public function __invoke(Request $request)
    {
        $filePath = Ignition::scripts()[$request->style];

        $content = file_get_contents($filePath);

        return response($content, 200, [
            'Content-Type' => 'text/css',
        ]);
    }
}
