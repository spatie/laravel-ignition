<?php

namespace Spatie\LaravelIgnition\Tests\Solutions\SolutionProviders;

use Livewire\Exceptions\MethodNotFoundException;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\UndefinedLivewireMethodSolutionProvider;
use Spatie\LaravelIgnition\Tests\stubs\Components\TestLivewireComponent;
use Spatie\LaravelIgnition\Tests\TestCase;
use Spatie\LaravelIgnition\Tests\TestClasses\FakeLivewireManager;

class UndefinedLivewireMethodSolutionProviderTest extends TestCase
{
    /** @test */
    public function it_can_solve_an_unknown_livewire_method()
    {
        FakeLivewireManager::setUp()->addAlias('test-livewire-component', TestLivewireComponent::class);

        $exception = new MethodNotFoundException('chnge', 'test-livewire-component');

        $canSolve = app(UndefinedLivewireMethodSolutionProvider::class)->canSolve($exception);
        [$solution] = app(UndefinedLivewireMethodSolutionProvider::class)->getSolutions($exception);

        $this->assertTrue($canSolve);

        $this->assertSame('Possible typo `Spatie\LaravelIgnition\Tests\stubs\Components\TestLivewireComponent::chnge`', $solution->getSolutionTitle());
        $this->assertSame('Did you mean `Spatie\LaravelIgnition\Tests\stubs\Components\TestLivewireComponent::change`?', $solution->getSolutionDescription());
    }
}
