<?php

namespace Spatie\LaravelIgnition\ContextProviders;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request as LaravelRequest;
use Spatie\FlareClient\Context\RequestContextProvider;
use Symfony\Component\HttpFoundation\Request as SymphonyRequest;
use Throwable;

class LaravelRequestContextProvider extends RequestContextProvider
{
    protected null|SymphonyRequest|LaravelRequest $request;

    public function __construct(LaravelRequest $request)
    {
        $this->request = $request;
    }

    /** @return array<string, mixed> */
    public function getUser(): array
    {
        try {
            /** @var object|null $user */
            /** @phpstan-ignore-next-line */
            $user = $this->request?->user();

            if (! $user) {
                return [];
            }
        } catch (Throwable) {
            return [];
        }

        try {
            if (method_exists($user, 'toFlare')) {
                return $user->toFlare();
            }

            if (method_exists($user, 'toArray')) {
                return $user->toArray();
            }
        } catch (Throwable $e) {
            return [];
        }

        return [];
    }

    /** @return array<string, mixed> */
    public function getRoute(): array
    {
        /** @phpstan-ignore-next-line */
        $route = $this->request->route();

        return [
            'route' => optional($route)->getName(),
            'routeParameters' => $this->getRouteParameters(),
            'controllerAction' => optional($route)->getActionName(),
            'middleware' => array_values(optional($route)->gatherMiddleware() ?? []),
        ];
    }

    /** @return array<int, mixed> */
    protected function getRouteParameters(): array
    {
        try {
            /** @phpstan-ignore-next-line */
            return collect(optional($this->request->route())->parameters ?? [])
                ->map(fn ($parameter) => $parameter instanceof Model ? $parameter->withoutRelations() : $parameter)
                ->map(function ($parameter){
                    return method_exists($parameter, 'toFlare') ? $parameter->toFlare() : $parameter;
                })
                ->toArray();
        } catch (Throwable) {
            return [];
        }
    }

    /** @return array<int, mixed> */
    public function toArray(): array
    {
        $properties = parent::toArray();

        $properties['route'] = $this->getRoute();

        $properties['user'] = $this->getUser();

        return $properties;
    }
}
