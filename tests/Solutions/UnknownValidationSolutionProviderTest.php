<?php

namespace Spatie\LaravelIgnition\Tests\Solutions;

use BadMethodCallException;
use Exception;
use Illuminate\Support\Facades\Validator;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\UnknownValidationSolutionProvider;
use Spatie\LaravelIgnition\Tests\TestCase;

class UnknownValidationSolutionProviderTest extends TestCase
{
    /** @test */
    public function it_can_solve_the_exception()
    {
        $canSolve = app(UnknownValidationSolutionProvider::class)->canSolve($this->getBadMethodCallException());

        $this->assertTrue($canSolve);
    }

    /**
     * @test
     *
     * @dataProvider rulesProvider
     */
    public function it_can_recommend_changing_the_rule(string $invalidRule, string $recommendedRule)
    {
        Validator::extend('foo', fn ($attribute, $value, $parameters, $validator) => $value == 'foo');

        Validator::extendImplicit('bar_a', fn ($attribute, $value, $parameters, $validator) => $value == 'bar');

        /** @var \Spatie\IgnitionContracts\Solution $solution */
        $solution = app(UnknownValidationSolutionProvider::class)->getSolutions($this->getBadMethodCallException($invalidRule))[0];

        $this->assertEquals("Did you mean `{$recommendedRule}` ?", $solution->getSolutionDescription());
        $this->assertEquals('Unknown Validation Rule', $solution->getSolutionTitle());
    }

    protected function getBadMethodCallException(string $rule = 'number'): BadMethodCallException
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

    public function rulesProvider(): array
    {
        return [
            ['number', 'numeric'],
            ['unik', 'unique'],
            ['fooo', 'foo'],
            ['bar_b', 'bar_a'],
        ];
    }
}
