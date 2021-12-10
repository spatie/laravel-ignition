<?php

use Spatie\LaravelIgnition\Solutions\SolutionProviders\RunningLaravelDuskInProductionProvider;
use Spatie\LaravelIgnition\Tests\TestCase;

uses(TestCase::class);

it('can solve dusk in production exception', function () {
    $exception = generate_dusk_exception();
    $canSolve = app(RunningLaravelDuskInProductionProvider::class)->canSolve($exception);
    [$first_solution, $second_solution] = app(RunningLaravelDuskInProductionProvider::class)->getSolutions($exception);

    $this->assertTrue($canSolve);
    $this->assertSame($first_solution->getSolutionTitle(), 'Laravel Dusk should not be run in production.');
    $this->assertSame($first_solution->getSolutionDescription(), 'Install the dependencies with the `--no-dev` flag.');

    $this->assertSame($second_solution->getSolutionTitle(), 'Laravel Dusk can be run in other environments.');
    $this->assertSame($second_solution->getSolutionDescription(), 'Consider setting the `APP_ENV` to something other than `production` like `local` for example.');
});

// Helpers
function generate_dusk_exception(): Exception
{
    return new Exception('It is unsafe to run Dusk in production.');
}
