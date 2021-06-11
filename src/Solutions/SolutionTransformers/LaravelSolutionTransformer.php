<?php

namespace Spatie\LaravelIgnition\Solutions\SolutionTransformers;

use Spatie\Ignition\Solutions\SolutionTransformer;
use Spatie\IgnitionContracts\RunnableSolution;
use Spatie\LaravelIgnition\Http\Controllers\ExecuteSolutionController;
use Throwable;

class LaravelSolutionTransformer extends SolutionTransformer
{
    public function toArray(): array
    {
        $baseProperties = parent::toArray();

        if (! $this->isRunnable()) {
            return $baseProperties;
        }

        $runnableProperties = [
            'is_runnable' => true,
            'class' => get_class($this->solution),
            'title' => $this->solution->getSolutionTitle(),
            'description' => $this->solution->getSolutionDescription(),
            'links' => $this->solution->getDocumentationLinks(),
            'execute_endpoint' => $this->executeEndpoint(),
        ];

        return array_merge($baseProperties, $runnableProperties);
    }

    protected function isRunnable(): bool
    {
        if (! $this->solution instanceof RunnableSolution) {
            return false;
        }

        if ($this->executeEndpoint() === '') {
            return false;
        }

        return true;
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
