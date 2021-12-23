<?php

use Facade\Ignition\Context\LaravelRequestContext;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Route;
use Spatie\LaravelIgnition\ContextProviders\LaravelRequestContextProvider;

it('returns route name in context data', function () {
    $route = Route::get('/route/', fn () => null)->name('routeName');

    $request = test()->createRequest('GET', '/route');

    $route->bind($request);

    $request->setRouteResolver(fn () => $route);

    $context = new LaravelRequestContextProvider($request);

    $contextData = $context->toArray();

    expect($contextData['route']['route'])->toBe('routeName');
});

it('returns route parameters in context data', function () {
    $route = Route::get('/route/{parameter}/{otherParameter}', fn () => null);

    $request = test()->createRequest('GET', '/route/value/second');

    $route->bind($request);

    $request->setRouteResolver(function () use ($route) {
        return $route;
    });

    $context = new LaravelRequestContextProvider($request);

    $contextData = $context->toArray();

    $this->assertSame([
        'parameter' => 'value',
        'otherParameter' => 'second',
    ], $contextData['route']['routeParameters']);
});

it('will call the to flare method on route parameters when it exists', function () {
    $route = Route::get('/route/{user}', function ($user) {
    });

    $request = $this->createRequest('GET', '/route/1');

    $route->bind($request);

    $request->setRouteResolver(function () use ($route) {
        $route->setParameter('user', new class{
            public function toFlare(): array
            {
                return ['stripped'];
            }
        });

        return $route;
    });

    $context = new LaravelRequestContextProvider($request);

    $contextData = $context->toArray();

    $this->assertSame([
        'user' => ['stripped'],
    ], $contextData['route']['routeParameters']);
});

it('returns the url', function () {
    $request = test()->createRequest('GET', '/route', []);

    $context = new LaravelRequestContextProvider($request);

    $request = $context->getRequest();

    expect($request['url'])->toBe('http://localhost/route');
});

it('returns the cookies', function () {
    $request = test()->createRequest('GET', '/route', [], ['cookie' => 'noms']);

    $context = new LaravelRequestContextProvider($request);

    expect($context->getCookies())->toBe(['cookie' => 'noms']);
});

it('returns the authenticated user', function () {
    $user = new User();
    $user->forceFill([
        'id' => 1,
        'email' => 'john@example.com',
    ]);

    $request = test()->createRequest('GET', '/route', [], ['cookie' => 'noms']);
    $request->setUserResolver(fn () => $user);

    $context = new LaravelRequestContextProvider($request);
    $contextData = $context->toArray();

    expect($contextData['user'])->toBe($user->toArray());
});

it('the authenticated user model has a to flare method it will be used to collect user data', function () {
    $user = new class extends User {
        public function toFlare()
        {
            return ['id' => $this->id];
        }
    };

    $user->forceFill([
        'id' => 1,
        'email' => 'john@example.com',
    ]);

    $request = test()->createRequest('GET', '/route', [], ['cookie' => 'noms']);
    $request->setUserResolver(fn () => $user);

    $context = new LaravelRequestContextProvider($request);
    $contextData = $context->toArray();

    expect($contextData['user'])->toBe(['id' => $user->id]);
});

it('the authenticated user model has no matching method it will return no user data', function () {
    $user = new class {
    };

    $request = test()->createRequest('GET', '/route', [], ['cookie' => 'noms']);
    $request->setUserResolver(fn () => $user);

    $context = new LaravelRequestContextProvider($request);
    $contextData = $context->toArray();

    expect($contextData['user'])->toBe([]);
});

it('the authenticated user model is broken it will return no user data', function () {
    $user = new class extends User {
        protected $appends = ['invalid'];
    };

    $request = test()->createRequest('GET', '/route', [], ['cookie' => 'noms']);
    $request->setUserResolver(fn () => $user);

    $context = new LaravelRequestContextProvider($request);
    $contextData = $context->toArray();

    expect($contextData['user'])->toBe([]);
});
