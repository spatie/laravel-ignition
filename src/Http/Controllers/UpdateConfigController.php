<?php

namespace Spatie\LaravelIgnition\Http\Controllers;

use Illuminate\Foundation\Validation\ValidatesRequests;
use Spatie\Ignition\Config\IgnitionConfig;
use Spatie\LaravelIgnition\Http\Requests\UpdateConfigRequest;

class UpdateConfigController
{
    use ValidatesRequests;

    public function __invoke(UpdateConfigRequest $request)
    {
        $result = (new IgnitionConfig())->saveValues($request->validated());

        return response()->json($result);
    }
}
