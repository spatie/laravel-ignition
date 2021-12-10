<?php

use Spatie\Ignition\Solutions\SolutionProviders\UndefinedPropertySolutionProvider;

it('can solve an undefined property exception when there is a similar property', function () {
    $canSolve = app(UndefinedPropertySolutionProvider::class)->canSolve(getUndefinedPropertyException());

    expect($canSolve)->toBeTrue();
});

it('cannot solve an undefined property exception when there is no similar property', function () {
    $canSolve = app(UndefinedPropertySolutionProvider::class)->canSolve(getUndefinedPropertyException('balance'));

    expect($canSolve)->toBeFalse();
});

it('can recommend a property name when there is a similar property', function () {
    /** @var \Spatie\Ignition\Contracts\Solution $solution */
    $solution = app(UndefinedPropertySolutionProvider::class)->getSolutions(getUndefinedPropertyException())[0];

    expect($solution->getSolutionDescription())->toEqual('Did you mean Spatie\LaravelIgnition\Tests\Support\Models\Car::$color ?');
});

it('cannot recommend a property name when there is no similar property', function () {
    /** @var \Spatie\Ignition\Contracts\Solution $solution */
    $solution = app(UndefinedPropertySolutionProvider::class)->getSolutions(getUndefinedPropertyException('balance'))[0];

    expect($solution->getSolutionDescription())->toEqual('');
});

// Helpers
function getUndefinedPropertyException(string $property = 'colro'): ErrorException
{
    return new ErrorException("Undefined property: Spatie\LaravelIgnition\Tests\Support\Models\Car::$$property ");
}
