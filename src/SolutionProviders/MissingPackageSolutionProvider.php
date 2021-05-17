<?php

namespace Spatie\Ignition\SolutionProviders;

use Illuminate\Support\Str;
use Spatie\Ignition\Solutions\MissingPackageSolution;
use Spatie\Ignition\Support\Packagist\Package;
use Spatie\Ignition\Support\Packagist\Packagist;
use Spatie\IgnitionContracts\HasSolutionsForThrowable;
use Throwable;

class MissingPackageSolutionProvider implements HasSolutionsForThrowable
{
    /** @var \Spatie\Ignition\Support\Packagist\Package|null */
    protected $package;

    public function canSolve(Throwable $throwable): bool
    {
        $pattern = '/Class \'([^\s]+)\' not found/m';

        if (! preg_match($pattern, $throwable->getMessage(), $matches)) {
            return false;
        }

        $class = $matches[1];

        if (Str::startsWith($class, app()->getNamespace())) {
            return false;
        }

        $this->package = $this->findPackageFromClassName($class);

        return ! is_null($this->package);
    }

    public function getSolutions(Throwable $throwable): array
    {
        return [new MissingPackageSolution($this->package)];
    }

    protected function findPackageFromClassName(string $missingClassName): ?Package
    {
        if (! $package = $this->findComposerPackageForClassName($missingClassName)) {
            return null;
        }

        return $package->hasNamespaceThatContainsClassName($missingClassName)
            ? $package
            : null;
    }

    protected function findComposerPackageForClassName(string $className): ?Package
    {
        $packages = Packagist::findPackagesForClassName($className);

        return $packages[0] ?? null;
    }
}
