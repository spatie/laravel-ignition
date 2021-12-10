<?php

use Spatie\LaravelIgnition\Solutions\SolutionProviders\RunningLaravelDuskInProductionProvider;

it('can solve dusk in production exception', function () {
    $exception = generate_dusk_exception();
    $canSolve = app(RunningLaravelDuskInProductionProvider::class)->canSolve($exception);
    [$first_solution, $second_solution] = app(RunningLaravelDuskInProductionProvider::class)->getSolutions($exception);

    expect($canSolve)->toBeTrue();
    expect('Laravel Dusk should not be run in production.')->toBe($first_solution->getSolutionTitle());
    expect('Install the dependencies with the `--no-dev` flag.')->toBe($first_solution->getSolutionDescription());

    expect('Laravel Dusk can be run in other environments.')->toBe($second_solution->getSolutionTitle());
    expect('Consider setting the `APP_ENV` to something other than `production` like `local` for example.')->toBe($second_solution->getSolutionDescription());
});

// Helpers
function generate_dusk_exception(): Exception
{
    return new Exception('It is unsafe to run Dusk in production.');
}
