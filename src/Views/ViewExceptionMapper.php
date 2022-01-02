<?php

namespace Spatie\LaravelIgnition\Views;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\View\ViewException;
use ReflectionProperty;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\LaravelIgnition\Exceptions\ViewException as IgnitionViewException;
use Spatie\LaravelIgnition\Exceptions\ViewExceptionWithSolution;
use Spatie\LaravelIgnition\Views\Compilers\BladeSourceMapCompiler;

class ViewExceptionMapper
{
    public function transform(ViewException $viewException): ViewException
    {
        $baseException = $viewException->getPrevious();
        while($baseException->getPrevious()) {
            $baseException = $baseException->getPrevious();
        }

//            dd($viewException, $baseException);

        $viewExceptionClass = \Spatie\LaravelIgnition\Exceptions\ViewException::class;

        if ($baseException instanceof $viewExceptionClass) {
            return $baseException;
        }

        if ($baseException instanceof ProvidesSolution) {
            $viewExceptionClass = ViewExceptionWithSolution::class;
        }

        preg_match('/\(View: (?P<path>.*?)\)/', $viewException->getMessage(), $matches);

//            dd($baseException, $matches['path']);
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

    protected function modifyViewsInTrace(\Spatie\LaravelIgnition\Exceptions\ViewException $exception, $baseException): void
    {
//        dd($baseException->getTrace());
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

    protected function findCompiledView(string $compiledPath): ?string
    {
        $resolver = $this->app->make('view.engine.resolver');
        $bladeEngine = $resolver->resolve('blade');
        /** @var BladeCompiler $compiler */
        $compiler = $bladeEngine->getCompiler();

        $lastCompiled = new ReflectionProperty($bladeEngine, 'lastCompiled');
        $lastCompiled->setAccessible(true);
        $lastCompiled = $lastCompiled->getValue($bladeEngine);

        $knownPaths = [];
        foreach($lastCompiled as $lastCompiledPath) {
            $knownPaths[$compiler->getCompiledPath($lastCompiledPath)] = $lastCompiledPath;
        }

        return $knownPaths[$compiledPath] ?? null;
    }
}
