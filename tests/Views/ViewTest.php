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

it('maps exceptions around php and verbatim blocks to the original blade line', function (string $view, int $line) {
    try {
        view($view)->render();
    } catch (ViewException $exception) {
        $mapped = app(ViewExceptionMapper::class)->map($exception);

        expect($mapped->getFile())->toBe(realpath(__DIR__ . "/../stubs/views/{$view}.blade.php"))
            ->and($mapped->getLine())->toBe($line);

        return;
    }

    $this->fail('The view should throw an exception.');
})->with([
    'inside a php block' => ['blade-exception-php-block', 7],
    'after a verbatim block containing php' => ['blade-exception-after-verbatim-php', 11],
    'after a php block containing a verbatim block' => ['blade-exception-after-php-block-with-verbatim', 9],
]);

it('maps php block failures without changing Blade compilation boundaries', function (string $source, int $line) {
    $path = sys_get_temp_dir() . '/ignition-' . bin2hex(random_bytes(8)) . '.blade.php';
    file_put_contents($path, $source);

    try {
        view()->file($path)->render();
    } catch (ViewException $exception) {
        $mapped = app(ViewExceptionMapper::class)->map($exception);

        expect($mapped->getFile())->toBe(realpath($path))
            ->and($mapped->getLine())->toBe($line)
            ->and($mapped->getMessage())->toBe('Call to undefined function somethingBadHappens()');

        return;
    } finally {
        unlink($path);
    }

    $this->fail('The view should throw an exception.');
})->with([
    'unicode, CRLF, blank lines and multiple blocks' => [
        "雪\r\n@php\r\n    \$value = 'é';\r\n@endphp\r\n\r\n@php\r\n\r\n    somethingBadHappens();\r\n@endphp",
        8,
    ],
    'escaped directive before a block' => [
        "@@php\n<p>Before</p>\n@php\n    \$value = 1;\n    somethingBadHappens();\n@endphp",
        5,
    ],
    'multiline strings, comments, heredocs and nowdocs' => [
        <<<'BLADE'
<p>Before</p>
@php
    $quoted = "first
second";
    /* comment
       continuation */
    $heredoc = <<<TEXT
    first
    second
    TEXT;
    $nowdoc = <<<'TEXT'
    first
    second
    TEXT;
    somethingBadHappens();
@endphp
BLADE,
        15,
    ],
    'failure after a component and verbatim containing an escaped block' => [
        "<p>Before</p>\n@verbatim\n@@php\nignored\n@endphp\n@endverbatim\n<x-title title=\"雪\" />\n@php\n    \$value = 1;\n    somethingBadHappens();\n@endphp",
        10,
    ],
    'failure on a multiline expression inside a block' => [
        "<p>Before</p>\n@php\n    \$value = strlen(\n        somethingBadHappens()\n    );\n@endphp",
        4,
    ],
    'echo after a block with multiline content' => [
        "@php\n    \$value = 'first\nsecond';\n@endphp\n\n{!! somethingBadHappens() !!}",
        6,
    ],
]);

it('renders the base exception solutions', function () {
    $this->get('/solution-exception')
        ->assertSee('This is a solution');
});

it('does not render a view exception wrapper instead of the original php exception', function () {
    $this->get('/php-exception')
        ->assertSee('Error: Call to undefined function somethingBadHappens()');
});
