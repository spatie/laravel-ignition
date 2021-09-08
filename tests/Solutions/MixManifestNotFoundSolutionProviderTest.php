<?php


namespace Spatie\LaravelIgnition\Tests\Solutions;

use Exception;
use Illuminate\Support\Str;
use Spatie\LaravelIgnition\Solutions\SolutionProviders\MissingMixManifestSolutionProvider;
use Spatie\LaravelIgnition\Tests\TestCase;

class MixManifestNotFoundSolutionProviderTest extends TestCase
{
    /** @test */
    public function it_can_solve_a_missing_mix_manifest_exception()
    {
        $canSolve = app(MissingMixManifestSolutionProvider::class)
            ->canSolve(new Exception('The Mix manifest does not exist.'));

        $this->assertTrue($canSolve);
    }

    /** @test */
    public function it_can_recommend_running_npm_install_and_npm_run_dev()
    {
        /** @var \Spatie\IgnitionContracts\Solution $solution */
        $solution = app(MissingMixManifestSolutionProvider::class)
            ->getSolutions(new Exception('The Mix manifest does not exist.'))[0];

        $this->assertTrue(Str::contains($solution->getSolutionDescription(), 'Did you forget to run `npm ci && npm run dev`?'));
    }
}
