<?php

namespace Spatie\Ignition\Support\Packagist;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Package
{
    public string $name;

    public string $url;

    public string $repository;

    public function __construct(array $properties)
    {
        $this->name = $properties['name'];

        $this->url = $properties['url'];

        $this->repository = $properties['repository'];
    }

    public function hasNamespaceThatContainsClassName(string $className): bool
    {
        return $this
            ->getNamespaces()
            ->contains(fn($namespace) => Str::startsWith(strtolower($className), strtolower($namespace)));
    }

    protected function getNamespaces(): Collection
    {
        $details = json_decode(file_get_contents("https://packagist.org/packages/{$this->name}.json"), true);

        return collect($details['package']['versions'])
            ->map(function ($version) {
                return collect($version['autoload'] ?? [])
                    ->map(fn($autoload) => array_keys($autoload))
                    ->flatten();
            })
            ->flatten()
            ->unique();
    }
}
