<?php

use Livewire\Exceptions\PropertyNotFoundException;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\UndefinedLivewirePropertySolutionProvider;
use Spatie\LaravelIgnition\Tests\stubs\Components\TestLivewireComponent;
use Spatie\LaravelIgnition\Tests\TestCase;
use Spatie\LaravelIgnition\Tests\TestClasses\FakeLivewireManager;

uses(TestCase::class);

it('can solve an unknown livewire computed property', function () {
    FakeLivewireManager::setUp()->addAlias('test-livewire-component', TestLivewireComponent::class);

    $exception = new PropertyNotFoundException('compted', 'test-livewire-component');

    $canSolve = app(UndefinedLivewirePropertySolutionProvider::class)->canSolve($exception);
    [$solution] = app(UndefinedLivewirePropertySolutionProvider::class)->getSolutions($exception);

    $this->assertTrue($canSolve);

    $this->assertSame('Possible typo $compted', $solution->getSolutionTitle());
    $this->assertSame('Did you mean `$computed`?', $solution->getSolutionDescription());
});

// Helpers
function it_can_solve_an_unknown_livewire_property()
{
    FakeLivewireManager::setUp()->addAlias('test-livewire-component', TestLivewireComponent::class);

    $exception = new PropertyNotFoundException('strng', 'test-livewire-component');

    $canSolve = app(UndefinedLivewirePropertySolutionProvider::class)->canSolve($exception);
    [$firstSolution, $secondSolution] = app(UndefinedLivewirePropertySolutionProvider::class)->getSolutions($exception);

    test()->assertTrue($canSolve);

    test()->assertSame('Possible typo $strng', $firstSolution->getSolutionTitle());
    test()->assertSame('Did you mean `$string`?', $firstSolution->getSolutionDescription());

    test()->assertSame('Possible typo $strng', $secondSolution->getSolutionTitle());
    test()->assertSame('Did you mean `$stringable`?', $secondSolution->getSolutionDescription());
}
