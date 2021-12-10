<?php

use Livewire\Exceptions\MethodNotFoundException;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\UndefinedLivewireMethodSolutionProvider;
use Spatie\LaravelIgnition\Tests\stubs\Components\TestLivewireComponent;
use Spatie\LaravelIgnition\Tests\TestCase;
use Spatie\LaravelIgnition\Tests\TestClasses\FakeLivewireManager;


it('can solve an unknown livewire method', function () {
    FakeLivewireManager::setUp()->addAlias('test-livewire-component', TestLivewireComponent::class);

    $exception = new MethodNotFoundException('chnge', 'test-livewire-component');

    $canSolve = app(UndefinedLivewireMethodSolutionProvider::class)->canSolve($exception);
    [$solution] = app(UndefinedLivewireMethodSolutionProvider::class)->getSolutions($exception);

    expect($canSolve)->toBeTrue();

    expect($solution->getSolutionTitle())->toBe('Possible typo `Spatie\LaravelIgnition\Tests\stubs\Components\TestLivewireComponent::chnge`');
    expect($solution->getSolutionDescription())->toBe('Did you mean `Spatie\LaravelIgnition\Tests\stubs\Components\TestLivewireComponent::change`?');
});
