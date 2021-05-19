<?php

namespace Spatie\Ignition\Middleware;

use Spatie\FlareClient\Report;
use Spatie\IgnitionContracts\SolutionProviderRepository;

class AddSolutions
{
    protected SolutionProviderRepository $solutionProviderRepository;

    public function __construct(SolutionProviderRepository $solutionProviderRepository)
    {
        $this->solutionProviderRepository = $solutionProviderRepository;
    }

    public function handle(Report $report, $next)
    {
        if ($throwable = $report->getThrowable()) {
            $solutions = $this->solutionProviderRepository->getSolutionsForThrowable($throwable);

            foreach ($solutions as $solution) {
                $report->addSolution($solution);
            }
        }

        return $next($report);
    }
}
