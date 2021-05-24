<?php

namespace Spatie\LaravelIgnition\Solutions;

use Illuminate\Contracts\Support\Arrayable;
use Spatie\LaravelIgnition\Http\Controllers\ExecuteSolutionController;
use Spatie\IgnitionContracts\RunnableSolution;
use Spatie\IgnitionContracts\Solution;
use Throwable;

class SolutionTransformer implements Arrayable
{
    protected Solution $solution;

    public function __construct(Solution $solution)
    {
        $this->solution = $solution;
    }

    public function toArray(): array
    {
        $isRunnable = ($this->solution instanceof RunnableSolution);

        return [
            'class' => get_class($this->solution),
            'title' => $this->solution->getSolutionTitle(),
            'description' => $this->solution->getSolutionDescription(),
            'links' => $this->solution->getDocumentationLinks(),
            'is_runnable' => $isRunnable,
            'run_button_text' => $isRunnable ? $this->solution->getRunButtonText() : '',
            'run_parameters' => $isRunnable ? $this->solution->getRunParameters() : [],
            'action_description' => $isRunnable ? $this->solution->getSolutionActionDescription() : '',
            'execute_endpoint' => $this->executeEndpoint(),
        ];
    }

    protected function executeEndpoint(): string
    {
        try {
            return action(ExecuteSolutionController::class);
        } catch (Throwable $exception) {
            return '';
        }
    }
}
