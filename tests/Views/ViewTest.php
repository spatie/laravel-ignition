<?php

use Illuminate\Contracts\View\Engine;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\View\Engines\PhpEngine;
use Illuminate\View\ViewException;
use Spatie\LaravelIgnition\Views\ViewExceptionMapper;

beforeEach(function () {
    View::addLocation(__DIR__ . '/../stubs/views');

    config()->set('app.debug', true);

    Route::view('blade-exception', 'blade-exception');
    Route::view('php-exception', 'php-exception');
    Route::view('blade-exception-utf8', 'blade-exception-utf8');
    Route::view('solution-exception', 'solution-exception');
});

it('renders a view exception wrapper instead of the original blade exception', function () {
    $this->get('/blade-exception')
        ->assertSee('Spatie\LaravelIgnition\Exceptions\ViewException');
});

it('renders the original line number in view exceptions', function () {
    $this->get('/blade-exception')
        ->assertSee('on line 3');
});

it('maps the original blade file and line through decorated engines', function (int $depth) {
    $resolver = app('view.engine.resolver');
    $engine = $resolver->resolve('blade');

    for ($index = 0; $index < $depth; $index++) {
        $engine = new class($engine) implements Engine {
            public function __construct(protected Engine $engine)
            {
            }

            public function get($path, array $data = []): string
            {
                return $this->engine->get($path, $data);
            }
        };
    }

    $resolver->register('blade', fn () => $engine);

    try {
        view('blade-exception')->render();
    } catch (ViewException $exception) {
        $mapped = app(ViewExceptionMapper::class)->map($exception);

        expect($mapped->getFile())->toBe(realpath(__DIR__ . '/../stubs/views/blade-exception.blade.php'))
            ->and($mapped->getLine())->toBe(3)
            ->and($mapped->getMessage())->toBe('Call to undefined function somethingBadHappens()')
            ->and($mapped->getPrevious())->toBe($exception->getPrevious());

        return;
    }

    $this->fail('The view should throw an exception.');
})->with([0, 1, 2, 3, 4, 10]);

it('preserves the original exception when a compiler cannot be unwrapped', function (string $decoration) {
    $engine = new class implements Engine {
        public ?Engine $engine = null;

        public function get($path, array $data = []): string
        {
            return '';
        }
    };

    if ($decoration === 'cycle') {
        $engine->engine = $engine;
    }

    if ($decoration === 'no decorator') {
        $engine = new PhpEngine(app('files'));
    }

    app('view.engine.resolver')->register('blade', fn () => $engine);

    $original = new RuntimeException('Original failure');
    $exception = new ViewException('Original failure (View: example.blade.php)', 0, 1, __FILE__, __LINE__, $original);
    $mapped = app(ViewExceptionMapper::class)->map($exception);

    expect($mapped->getMessage())->toBe('Original failure')
        ->and($mapped->getFile())->toBe($original->getFile())
        ->and($mapped->getLine())->toBe($original->getLine())
        ->and($mapped->getPrevious())->toBe($original)
        ->and($mapped->getView())->toBe('example.blade.php');
})->with(['cycle', 'null engine', 'no decorator']);

it('preserves view exceptions without a view path in the message', function () {
    $exception = new ViewException('Custom view failure', 0, 1, __FILE__, __LINE__);
    $mapped = app(ViewExceptionMapper::class)->map($exception);

    expect($mapped->getMessage())->toBe('Custom view failure')
        ->and($mapped->getFile())->toBe($exception->getFile())
        ->and($mapped->getLine())->toBe($exception->getLine())
        ->and($mapped->getView())->toBe($exception->getFile())
        ->and($mapped->getPrevious())->toBe($exception);
});

it('renders the original line number in view exceptions with utf8 characters', function () {
    $this->get('/blade-exception-utf8')
        ->assertSee('on line 11');
});

it('renders the base exception solutions', function () {
    $this->get('/solution-exception')
        ->assertSee('This is a solution');
});

it('does not render a view exception wrapper instead of the original php exception', function () {
    $this->get('/php-exception')
        ->assertSee('Error: Call to undefined function somethingBadHappens()');
});
