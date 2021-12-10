<?php

use Livewire\Exceptions\MethodNotFoundException;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\UndefinedLivewireMethodSolutionProvider;
use Spatie\LaravelIgnition\Tests\stubs\Components\TestLivewireComponent;
use Spatie\LaravelIgnition\Tests\TestCase;
use Spatie\LaravelIgnition\Tests\TestClasses\FakeLivewireManager;

uses(TestCase::class);

it('can solve an unknown livewire method', function () {
    FakeLivewireManager::setUp()->addAlias('test-livewire-component', TestLivewireComponent::class);

    $exception = new MethodNotFoundException('chnge', 'test-livewire-component');

    $canSolve = app(UndefinedLivewireMethodSolutionProvider::class)->canSolve($exception);
    [$solution] = app(UndefinedLivewireMethodSolutionProvider::class)->getSolutions($exception);

    $this->assertTrue($canSolve);

    $this->assertSame('Possible typo `Spatie\LaravelIgnition\Tests\stubs\Components\TestLivewireComponent::chnge`', $solution->getSolutionTitle());
    $this->assertSame('Did you mean `Spatie\LaravelIgnition\Tests\stubs\Components\TestLivewireComponent::change`?', $solution->getSolutionDescription());
});
