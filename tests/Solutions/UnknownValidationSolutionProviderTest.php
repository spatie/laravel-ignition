<?php

use Illuminate\Support\Facades\Validator;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\UnknownValidationSolutionProvider;

it('can solve the exception', function () {
    $canSolve = app(UnknownValidationSolutionProvider::class)->canSolve(getBadMethodCallException());

    expect($canSolve)->toBeTrue();
});

it('can recommend changing the rule', function (string $invalidRule, string $recommendedRule) {
    Validator::extend('foo', fn ($attribute, $value, $parameters, $validator) => $value == 'foo');

    Validator::extendImplicit('bar_a', fn ($attribute, $value, $parameters, $validator) => $value == 'bar');

    /** @var \Spatie\Ignition\Contracts\Solution $solution */
    $solution = app(UnknownValidationSolutionProvider::class)->getSolutions(getBadMethodCallException($invalidRule))[0];

    expect($solution->getSolutionDescription())->toEqual("Did you mean `{$recommendedRule}` ?");
    expect($solution->getSolutionTitle())->toEqual('Unknown Validation Rule');
})->with('rulesProvider');

// Datasets
dataset('rulesProvider', [
    ['number', 'numeric'],
    ['unik', 'unique'],
    ['fooo', 'foo'],
    ['bar_b', 'bar_a'],
]);

// Helpers
function getBadMethodCallException(string $rule = 'number'): BadMethodCallException
{
    $default = new BadMethodCallException('Not a validation rule exception!');

    try {
        $validator = Validator::make(['number' => 10], ['number' => "{$rule}"]);
        $validator->validate();

        return $default;
    } catch (BadMethodCallException $badMethodCallException) {
        return $badMethodCallException;
    } catch (Exception $exception) {
        return $default;
    }
}
