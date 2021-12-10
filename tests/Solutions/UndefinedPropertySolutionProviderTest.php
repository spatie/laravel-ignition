<?php

use Spatie\Ignition\Solutions\SolutionProviders\UndefinedPropertySolutionProvider;
use Spatie\LaravelIgnition\Tests\TestCase;

uses(TestCase::class);

it('can solve an undefined property exception when there is a similar property', function () {
    $canSolve = app(UndefinedPropertySolutionProvider::class)->canSolve(getUndefinedPropertyException());

    $this->assertTrue($canSolve);
});

it('cannot solve an undefined property exception when there is no similar property', function () {
    $canSolve = app(UndefinedPropertySolutionProvider::class)->canSolve(getUndefinedPropertyException('balance'));

    $this->assertFalse($canSolve);
});

it('can recommend a property name when there is a similar property', function () {
    /** @var \Spatie\Ignition\Contracts\Solution $solution */
    $solution = app(UndefinedPropertySolutionProvider::class)->getSolutions(getUndefinedPropertyException())[0];

    $this->assertEquals('Did you mean Spatie\LaravelIgnition\Tests\Support\Models\Car::$color ?', $solution->getSolutionDescription());
});

it('cannot recommend a property name when there is no similar property', function () {
    /** @var \Spatie\Ignition\Contracts\Solution $solution */
    $solution = app(UndefinedPropertySolutionProvider::class)->getSolutions(getUndefinedPropertyException('balance'))[0];

    $this->assertEquals('', $solution->getSolutionDescription());
});

// Helpers
function getUndefinedPropertyException(string $property = 'colro'): ErrorException
{
    return new ErrorException("Undefined property: Spatie\LaravelIgnition\Tests\Support\Models\Car::$$property ");
}
