<?php

namespace Spatie\LaravelIgnition\Tests\Exceptions;

use Illuminate\Foundation\Exceptions\Handler;
use Whoops\Handler\HandlerInterface;

class ExceptionHandler extends Handler
{
    protected function whoopsHandler()
    {
        return app(HandlerInterface::class);
    }
}
