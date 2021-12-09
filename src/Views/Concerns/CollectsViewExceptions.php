<?php

namespace Spatie\LaravelIgnition\Views\Concerns;

use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\View\Engines\CompilerEngine;

trait CollectsViewExceptions
{
    /** @var array<int|string, mixed> */
    protected array $lastCompiledData = [];

    /**
     * @param string $path
     * @param array<string, mixed> $data
     *
     * @return void
     */
    public function collectViewData(string $path, array $data): void
    {
        $this->lastCompiledData[] = [
            'path' => $path,
            'compiledPath' => $this->getCompiledPath($path),
            'data' => $this->filterViewData($data),
        ];
    }

    /**
     * @param  array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function filterViewData(array $data): array
    {
        // By default, Laravel views get two d data keys:
        // __env and app. We try to filter them out.
        return array_filter($data, function ($value, $key) {
            if ($key === 'app') {
                return ! $value instanceof Application;
            }

            return $key !== '__env';
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @param string $compiledPath
     *
     * @return array<string, mixed>
     */
    public function getCompiledViewData(string $compiledPath): array
    {
        $compiledView = $this->findCompiledView($compiledPath);

        return $compiledView['data'] ?? [];
    }

    public function getCompiledViewName(string $compiledPath): string
    {
        $compiledView = $this->findCompiledView($compiledPath);

        return $compiledView['path'] ?? $compiledPath;
    }

    /**
     * @param string $compiledPath
     *
     * @return null|array<string, mixed>
     */
    protected function findCompiledView(string $compiledPath): ?array
    {
        return Collection::make($this->lastCompiledData)
            ->first(function ($compiledData) use ($compiledPath) {
                $comparePath = $compiledData['compiledPath'];

                return realpath(dirname($comparePath)).DIRECTORY_SEPARATOR.basename($comparePath) === $compiledPath;
            });
    }

    protected function getCompiledPath(string $path): string
    {
        if ($this instanceof CompilerEngine) {
            return $this->getCompiler()->getCompiledPath($path);
        }

        return $path;
    }
}
