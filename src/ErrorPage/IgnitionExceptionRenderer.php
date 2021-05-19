<?php

namespace Spatie\Ignition\ErrorPage;

use Illuminate\Contracts\Foundation\ExceptionRenderer;

class IgnitionExceptionRenderer implements ExceptionRenderer
{
    protected ErrorPageHandler $errorPageHandler;

    public function __construct(ErrorPageHandler $errorPageHandler)
    {
        $this->errorPageHandler = $errorPageHandler;
    }

    public function render($throwable)
    {
        ob_start();

        $this->errorPageHandler->handle($throwable);

        return ob_get_clean();
    }
}
