<?php

namespace Spatie\LaravelIgnition\Views;

use Exception;
use Illuminate\Filesystem\Filesystem;
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
    private CompilerEngine $compilerEngine;

    public function __construct()
    {
        $resolver = app('view.engine.resolver');

        $this->compilerEngine = $resolver->resolve('blade');
    }

    public function map(ViewException $viewException): IgnitionViewException
    {
//        dd($viewException);
        $baseException = $this->getRootException($viewException);

        if ($baseException instanceof IgnitionViewException) {
            return $baseException;
        }

        $viewExceptionClass = $baseException instanceof ProvidesSolution
            ? ViewExceptionWithSolution::class
            : IgnitionViewException::class;

        preg_match('/\(View: (?P<path>.*?)\)/', $viewException->getMessage(), $matches);

        $compiledViewPath = $matches['path'];

        $sourceMapCompiler = new BladeSourceMapCompiler(app(Filesystem::class), 'not-needed');

        $bladeLineNumber = $sourceMapCompiler->detectLineNumber($compiledViewPath, $baseException->getLine());

        $exception = new $viewExceptionClass(
            $baseException->getMessage(),
            0,
            1,
            $compiledViewPath,
            $bladeLineNumber,
            $baseException
        );

        if ($baseException instanceof ProvidesSolution) {
            /** @var ViewExceptionWithSolution $exception */
            $exception->setSolution($baseException->getSolution());
        }

        $this->modifyViewsInTrace($exception, $baseException);

        $exception->setView($compiledViewPath);
        $exception->setViewData([]);

        return $exception;
    }

    protected function modifyViewsInTrace(
        \Spatie\LaravelIgnition\Exceptions\ViewException $exception,
        $baseException
    ): void {
        $trace = Collection::make($baseException->getTrace())
            ->map(function ($trace) {
                if ($originalPath = $this->findCompiledView(Arr::get($trace, 'file', ''))) {
                    $trace['file'] = $originalPath;
//                    $trace['line'] = $this->getBladeLineNumber($trace['file'], $trace['line']);
                }

                return $trace;
            })->toArray();

        $traceProperty = new ReflectionProperty('Exception', 'trace');
        $traceProperty->setAccessible(true);
        $traceProperty->setValue($exception, $trace);
    }

    protected function getRootException(Throwable $exception): Throwable
    {
        // Find the first exception that is not a ViewException

        $rootException = $exception->getPrevious() ?? $exception;

        while ($rootException->getPrevious()) {
            $rootException = $rootException->getPrevious();
        }

        return $rootException;
    }

    protected function findCompiledView(string $compiledPath): ?string
    {
        static $knownPaths = null;

        if (! $knownPaths) {
            $lastCompiled = new ReflectionProperty($this->compilerEngine, 'lastCompiled');
            $lastCompiled->setAccessible(true);
            $lastCompiled = $lastCompiled->getValue($this->compilerEngine);

            $knownPaths = [];
            foreach ($lastCompiled as $lastCompiledPath) {
                $compiledPath = $this->compilerEngine->getCompiler()->getCompiledPath($lastCompiledPath);

                $knownPaths[$compiledPath ?? $lastCompiledPath] = $lastCompiledPath;
            }
        }

        return $knownPaths[$compiledPath] ?? null;
    }
}
