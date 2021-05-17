<?php

namespace Spatie\Ignition\Tests\Solutions;

use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Spatie\Ignition\Exceptions\ViewException;
use Spatie\Ignition\SolutionProviders\UndefinedVariableSolutionProvider;
use Spatie\Ignition\Support\ComposerClassMap;
use Spatie\Ignition\Tests\TestCase;

class UndefinedVariableSolutionProviderTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        View::addLocation(__DIR__.'/../stubs/views');

        $this->app->bind(
            ComposerClassMap::class,
            function () {
                return new ComposerClassMap(__DIR__.'/../../vendor/autoload.php');
            }
        );
    }

    /** @test */
    public function it_can_solve_the_exception()
    {
        $canSolve = app(UndefinedVariableSolutionProvider::class)->canSolve($this->getUndefinedVariableException());

        $this->assertTrue($canSolve);
    }

    /** @test */
    public function it_can_recommend_fixing_a_variable_name_typo()
    {
        $viewData = [
            'footerDescription' => 'foo',
        ];

        try {
            view('undefined-variable-1', $viewData)->render();
        } catch (ViewException $exception) {
            $viewException = $exception;
        }

        $canSolve = app(UndefinedVariableSolutionProvider::class)->canSolve($viewException);
        $this->assertTrue($canSolve);

        /** @var \Spatie\IgnitionContracts\Solution $solution */
        $solutions = app(UndefinedVariableSolutionProvider::class)->getSolutions($viewException);
        $this->assertTrue(Str::contains($solutions[0]->getSolutionDescription(), 'Did you mean `$footerDescription`?'));
    }

    protected function getUndefinedVariableException(): ViewException
    {
        return new ViewException('Undefined variable: notSet (View: ./views/welcome.blade.php)');
    }
}
