<?php

namespace Spatie\LaravelIgnition\Solutions\SolutionProviders;

use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\HasSolutionsForThrowable;
use Spatie\LaravelIgnition\Exceptions\ViewException;
use Spatie\LaravelIgnition\Solutions\MakeViewVariableOptionalSolution;
use Spatie\LaravelIgnition\Solutions\SuggestCorrectVariableNameSolution;
use Throwable;

class UndefinedViewVariableSolutionProvider implements HasSolutionsForThrowable
{
    protected $variableName;

    protected $viewFile;

    public function canSolve(Throwable $throwable): bool
    {
        if (! $throwable instanceof ViewException) {
            return false;
        }

        return $this->getNameAndView($throwable) !== null;
    }

    public function getSolutions(Throwable $throwable): array
    {
        $solutions = [];

        extract($this->getNameAndView($throwable));

        if (! isset($variableName)) {
            return [];
        }

        $solutions = $this->findCorrectVariableSolutions($throwable, $variableName, $viewFile);
        $solutions[] = $this->findOptionalVariableSolution($variableName, $viewFile);

        return $solutions;
    }

    protected function findCorrectVariableSolutions(
        ViewException $throwable,
        string $variableName,
        string $viewFile
    ): array {
        return collect($throwable->getViewData())
            ->map(function ($value, $key) use ($variableName) {
                similar_text($variableName, $key, $percentage);

                return ['match' => $percentage, 'value' => $value];
            })
            ->sortByDesc('match')
            ->filter(fn ($var) => $var['match'] > 40)
            ->keys()
            ->map(fn ($suggestion) => new SuggestCorrectVariableNameSolution($variableName, $viewFile, $suggestion))
            ->map(function ($solution) {
                return $solution->isRunnable()
                    ? $solution
                    : BaseSolution::create($solution->getSolutionTitle())
                        ->setSolutionDescription($solution->getSolutionDescription());
            })
            ->toArray();
    }

    protected function findOptionalVariableSolution(string $variableName, string $viewFile)
    {
        $optionalSolution = new MakeViewVariableOptionalSolution($variableName, $viewFile);

        return $optionalSolution->isRunnable()
            ? $optionalSolution
            : BaseSolution::create($optionalSolution->getSolutionTitle())
                ->setSolutionDescription($optionalSolution->getSolutionDescription());
    }

    protected function getNameAndView(Throwable $throwable): ?array
    {
        $pattern = '/Undefined variable:? (.*?) \(View: (.*?)\)/';

        preg_match($pattern, $throwable->getMessage(), $matches);

        if (count($matches) === 3) {
            [, $variableName, $viewFile] = $matches;
            $variableName = ltrim($variableName, '$');

            return compact('variableName', 'viewFile');
        }

        return null;
    }
}
