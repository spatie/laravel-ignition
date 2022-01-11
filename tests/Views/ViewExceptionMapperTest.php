<?php

use Illuminate\Support\Facades\View;
use Spatie\LaravelIgnition\Exceptions\ViewException;

beforeEach(function () {
    View::addLocation(__DIR__ . '/../stubs/views');
});

it('adds additional blade information to the exception', function () {
    $viewData = [
        'super_specific_key' => 'super_specific_value',
    ];

    $mappedException = renderViewAndMapException('blade-exception', $viewData);

    $this->assertEquals($viewData, $mappedException?->getViewData());
});

function renderViewAndMapException(string $view, ?array $data = null): ViewException
{
    try {
        view($view, $data)->render();
    } catch (\Illuminate\View\ViewException $originalViewException) {
        $mappedException = app(\Spatie\LaravelIgnition\Views\ViewExceptionMapper::class)
            ->map($originalViewException);
    }

    if (! $mappedException) {
        test()->fail("No view exception was thrown in `${$view}`.");
    }

    return $mappedException;
}
