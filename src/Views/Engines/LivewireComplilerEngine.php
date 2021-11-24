<?php

namespace Spatie\LaravelIgnition\Views\Engines;

use Illuminate\View\Engines\PhpEngine;
use Livewire\ComponentConcerns\RendersLivewireComponents;
use Throwable;

class LivewireComplilerEngine extends CompilerEngine
{
    use RendersLivewireComponents;

    protected function handleViewException(Throwable $e, $obLevel)
    {
        if ($this->shouldBypassExceptionForLivewire($e, $obLevel)) {
            // On Laravel 7 and before, there is no files property on the underlying
            // Illuminate\Views\Engines\CompilerEngine class, so pass null in this case
            (new PhpEngine($this->files ?? null))->handleViewException($e, $obLevel);

            return;
        }

        parent::handleViewException($e, $obLevel);
    }
}
