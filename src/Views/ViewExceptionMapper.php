<?php

namespace Spatie\LaravelIgnition\Views;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\View\Engines\CompilerEngine;
use Illuminate\View\ViewException;
use ReflectionProperty;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\LaravelIgnition\Exceptions\ViewException as IgnitionViewException;
use Spatie\LaravelIgnition\Exceptions\ViewExceptionWithSolution;
use Spatie\LaravelIgnition\Views\Compilers\BladeSourceMapCompiler;
use Throwable;

class ViewExceptionMapper
{
    protected CompilerEngine $compilerEngine;
    protected BladeSourceMapCompiler $bladeSourceMapCompiler;

    public function __construct(BladeSourceMapCompiler $bladeSourceMapCompiler)
    {
        $resolver = app('view.engine.resolver');

        $this->compilerEngine = $resolver->resolve('blade');

        $this->bladeSourceMapCompiler = $bladeSourceMapCompiler;
    }

    public function map(ViewException $viewException): IgnitionViewException
    {
        $baseException = $this->getRealException($viewException);

        if ($baseException instanceof IgnitionViewException) {
            return $baseException;
        }

        $viewExceptionClass = $baseException instanceof ProvidesSolution
            ? ViewExceptionWithSolution::class
            : IgnitionViewException::class;

        preg_match('/\(View: (?P<path>.*?)\)/', $viewException->getMessage(), $matches);

        $compiledViewPath = $matches['path'];

        $exception = new $viewExceptionClass(
            $baseException->getMessage(),
            0,
            1,
            $baseException->getFile(),
            $baseException->getLine(),
            $baseException
        );

        if ($baseException instanceof ProvidesSolution) {
            /** @var ViewExceptionWithSolution $exception */
            $exception->setSolution($baseException->getSolution());
        }

        $this->modifyViewsInTrace($exception);

        $exception->setView($compiledViewPath);
        $exception->setViewData([]);

        return $exception;
    }

    protected function modifyViewsInTrace(IgnitionViewException $exception): void
    {
        $trace = Collection::make($exception->getPrevious()->getTrace())
            ->map(function ($trace) {
                if ($originalPath = $this->findCompiledView(Arr::get($trace, 'file', ''))) {
//                    dump($originalPath, '--------', $trace['line'], $this->getBladeLineNumber($originalPath, $trace['line']));
                    $trace['file'] = $originalPath;
                    $trace['line'] = $this->getBladeLineNumber($trace['file'], $trace['line']);
                }

                return $trace;
            })->toArray();

        $traceProperty = new ReflectionProperty('Exception', 'trace');
        $traceProperty->setAccessible(true);
        $traceProperty->setValue($exception, $trace);
    }

    /**
     * Look at the previous exceptions to find the original exception.
     * This is usually the first Exception that is not a ViewException.
     */
    protected function getRealException(Throwable $exception): Throwable
    {
        $rootException = $exception->getPrevious() ?? $exception;

        while ($rootException instanceof ViewException && $rootException->getPrevious()) {
            $rootException = $rootException->getPrevious();
        }

        return $rootException;
    }

    protected function findCompiledView(string $compiledPath): ?string
    {
        static $knownPaths = null;

        if (! $knownPaths) {
            $knownPaths = $this->getKnownPaths();
        }

        return $knownPaths[$compiledPath] ?? null;
    }

    protected function getKnownPaths(): array
    {
        $lastCompiled = new ReflectionProperty($this->compilerEngine, 'lastCompiled');
        $lastCompiled->setAccessible(true);
        $lastCompiled = $lastCompiled->getValue($this->compilerEngine);

        $knownPaths = [];
        foreach ($lastCompiled as $lastCompiledPath) {
            $compiledPath = $this->compilerEngine->getCompiler()->getCompiledPath($lastCompiledPath);

            $knownPaths[$compiledPath ?? $lastCompiledPath] = $lastCompiledPath;
        }

        return $knownPaths;
    }

    protected function getBladeLineNumber(string $view, int $compiledLineNumber): int
    {
        return $this->bladeSourceMapCompiler->detectLineNumber($view, $compiledLineNumber);
    }
}
