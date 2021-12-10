<?php

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\ViewNotFoundSolutionProvider;
use Spatie\LaravelIgnition\Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    View::addLocation(__DIR__.'/../stubs/views');
});

it('can solve the exception', function () {
    $canSolve = app(ViewNotFoundSolutionProvider::class)->canSolve(getViewNotFoundException());

    $this->assertTrue($canSolve);
});

it('can recommend changing a typo in the view name', function () {
    /** @var \Spatie\Ignition\Contracts\Solution $solution */
    $solution = app(ViewNotFoundSolutionProvider::class)->getSolutions(getViewNotFoundException())[0];

    $this->assertTrue(Str::contains($solution->getSolutionDescription(), 'Did you mean `php-exception`?'));
});

it('wont recommend another controller class if the names are too different', function () {
    $unknownView = 'a-view-that-doesnt-exist-and-is-not-a-typo';

    /** @var \Spatie\Ignition\Contracts\Solution $solution */
    $solution = app(ViewNotFoundSolutionProvider::class)->getSolutions(getViewNotFoundException($unknownView))[0];

    $this->assertFalse(Str::contains($solution->getSolutionDescription(), 'Did you mean'));
});

// Helpers
function getViewNotFoundException(string $view = 'phpp-exceptionn'): InvalidArgumentException
{
    return new InvalidArgumentException("View [{$view}] not found.");
}
