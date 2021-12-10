<?php

use Illuminate\Foundation\Auth\User;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Solutions\SolutionProviders\BadMethodCallSolutionProvider;
use Spatie\Ignition\Solutions\SolutionProviders\SolutionProviderRepository;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\MissingAppKeySolutionProvider;
use Spatie\LaravelIgnition\Tests\Exceptions\AlwaysFalseSolutionProvider;
use Spatie\LaravelIgnition\Tests\Exceptions\AlwaysTrueSolutionProvider;

it('returns possible solutions', function () {
    $repository = new SolutionProviderRepository();

    $repository->registerSolutionProvider(AlwaysTrueSolutionProvider::class);
    $repository->registerSolutionProvider(AlwaysFalseSolutionProvider::class);

    $solutions = $repository->getSolutionsForThrowable(new Exception());

    $this->assertNotNull($solutions);
    expect($solutions)->toHaveCount(1);
    expect($solutions[0] instanceof BaseSolution)->toBeTrue();
});

it('returns possible solutions when registered together', function () {
    $repository = new SolutionProviderRepository();

    $repository->registerSolutionProviders([
        AlwaysTrueSolutionProvider::class,
        AlwaysFalseSolutionProvider::class,
    ]);

    $solutions = $repository->getSolutionsForThrowable(new Exception());

    $this->assertNotNull($solutions);
    expect($solutions)->toHaveCount(1);
    expect($solutions[0] instanceof BaseSolution)->toBeTrue();
});

it('can suggest bad method call exceptions', function () {
    if (version_compare(app()->version(), '5.6.3', '<')) {
        $this->markTestSkipped('Laravel version < 5.6.3 do not support bad method call solutions');
    }

    try {
        collect([])->faltten();
    } catch (Exception $exception) {
        $solution = new BadMethodCallSolutionProvider();

        expect($solution->canSolve($exception))->toBeTrue();
    }
});

it('can propose a solution for bad method call exceptions on collections', function () {
    try {
        collect([])->frist(fn ($item) => null);
    } catch (Exception $exception) {
        $solution = new BadMethodCallSolutionProvider();

        expect($solution->getSolutions($exception)[0]->getSolutionDescription())->toBe('Did you mean Illuminate\Support\Collection::first() ?');
    }
});

it('can propose a solution for bad method call exceptions on models', function () {
    try {
        $user = new User();
        $user->sarve();
    } catch (Exception $exception) {
        $solution = new BadMethodCallSolutionProvider();

        expect($solution->getSolutions($exception)[0]->getSolutionDescription())->toBe('Did you mean Illuminate\Foundation\Auth\User::save() ?');
    }
});

it('can propose a solution for missing app key exceptions', function () {
    $exception = new RuntimeException('No application encryption key has been specified.');

    $solution = new MissingAppKeySolutionProvider();

    expect($solution->getSolutions($exception)[0]->getSolutionActionDescription())->toBe('Generate your application encryption key using `php artisan key:generate`.');
});
