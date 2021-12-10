<?php

use Exception;
use Illuminate\Foundation\Auth\User;
use RuntimeException;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Solutions\SolutionProviders\BadMethodCallSolutionProvider;
use Spatie\Ignition\Solutions\SolutionProviders\SolutionProviderRepository;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\MissingAppKeySolutionProvider;
use Spatie\LaravelIgnition\Tests\Exceptions\AlwaysFalseSolutionProvider;
use Spatie\LaravelIgnition\Tests\Exceptions\AlwaysTrueSolutionProvider;

uses(TestCase::class);

it('returns possible solutions', function () {
    $repository = new SolutionProviderRepository();

    $repository->registerSolutionProvider(AlwaysTrueSolutionProvider::class);
    $repository->registerSolutionProvider(AlwaysFalseSolutionProvider::class);

    $solutions = $repository->getSolutionsForThrowable(new Exception());

    $this->assertNotNull($solutions);
    $this->assertCount(1, $solutions);
    $this->assertTrue($solutions[0] instanceof BaseSolution);
});

it('returns possible solutions when registered together', function () {
    $repository = new SolutionProviderRepository();

    $repository->registerSolutionProviders([
        AlwaysTrueSolutionProvider::class,
        AlwaysFalseSolutionProvider::class,
    ]);

    $solutions = $repository->getSolutionsForThrowable(new Exception());

    $this->assertNotNull($solutions);
    $this->assertCount(1, $solutions);
    $this->assertTrue($solutions[0] instanceof BaseSolution);
});

it('can suggest bad method call exceptions', function () {
    if (version_compare(app()->version(), '5.6.3', '<')) {
        $this->markTestSkipped('Laravel version < 5.6.3 do not support bad method call solutions');
    }

    try {
        collect([])->faltten();
    } catch (Exception $exception) {
        $solution = new BadMethodCallSolutionProvider();

        $this->assertTrue($solution->canSolve($exception));
    }
});

it('can propose a solution for bad method call exceptions on collections', function () {
    try {
        collect([])->frist(fn ($item) => null);
    } catch (Exception $exception) {
        $solution = new BadMethodCallSolutionProvider();

        $this->assertSame('Did you mean Illuminate\Support\Collection::first() ?', $solution->getSolutions($exception)[0]->getSolutionDescription());
    }
});

it('can propose a solution for bad method call exceptions on models', function () {
    try {
        $user = new User();
        $user->sarve();
    } catch (Exception $exception) {
        $solution = new BadMethodCallSolutionProvider();

        $this->assertSame('Did you mean Illuminate\Foundation\Auth\User::save() ?', $solution->getSolutions($exception)[0]->getSolutionDescription());
    }
});

it('can propose a solution for missing app key exceptions', function () {
    $exception = new RuntimeException('No application encryption key has been specified.');

    $solution = new MissingAppKeySolutionProvider();

    $this->assertSame('Generate your application encryption key using `php artisan key:generate`.', $solution->getSolutions($exception)[0]->getSolutionActionDescription());
});
