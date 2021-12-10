<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\InvalidRouteActionSolutionProvider;
use Spatie\LaravelIgnition\Support\Composer\ComposerClassMap;
use Spatie\LaravelIgnition\Tests\stubs\Controllers\TestTypoController;
use Spatie\LaravelIgnition\Tests\TestCase;


beforeEach(function () {
    app()->bind(
        ComposerClassMap::class,
        function () {
            return new ComposerClassMap(__DIR__.'/../../vendor/autoload.php');
        }
    );
});

it('can solve the exception', function () {
    $canSolve = app(InvalidRouteActionSolutionProvider::class)->canSolve(getInvalidRouteActionException());

    expect($canSolve)->toBeTrue();
});

it('can recommend changing the routes method', function () {
    Route::get('/test', TestTypoController::class);

    /** @var \Spatie\Ignition\Contracts\Solution $solution */
    $solution = app(InvalidRouteActionSolutionProvider::class)->getSolutions(getInvalidRouteActionException())[0];

    expect(Str::contains($solution->getSolutionDescription(), 'Did you mean `TestTypoController`'))->toBeTrue();
});

it('wont recommend another controller class if the names are too different', function () {
    Route::get('/test', TestTypoController::class);

    $invalidController = 'UnrelatedTestTypoController';

    /** @var \Spatie\Ignition\Contracts\Solution $solution */
    $solution = app(InvalidRouteActionSolutionProvider::class)->getSolutions(getInvalidRouteActionException($invalidController))[0];

    expect(Str::contains($solution->getSolutionDescription(), 'Did you mean `TestTypoController`'))->toBeFalse();
});

// Helpers
function getInvalidRouteActionException(string $controller = 'TestTypooController'): UnexpectedValueException
{
    return new UnexpectedValueException("Invalid route action: [{$controller}]");
}
