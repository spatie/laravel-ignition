<?php

use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\View;
use Spatie\Ignition\Contracts\BaseSolution;
use Spatie\Ignition\Contracts\ProvidesSolution;
use Spatie\Ignition\Contracts\Solution;
use Spatie\LaravelIgnition\Exceptions\ViewException;
use Spatie\LaravelIgnition\Exceptions\ViewExceptionWithSolution;

uses(TestCase::class);

beforeEach(function () {
    View::addLocation(__DIR__.'/stubs/views');
});

it('detects blade view exceptions', function () {
    $this->expectException(ViewException::class);

    view('blade-exception')->render();
});

it('detects the original line number in view exceptions', function () {
    try {
        view('blade-exception')->render();
    } catch (ViewException $exception) {
        $this->assertSame(3, $exception->getLine());
    }
});

it('detects the original line number in view exceptions with utf8 characters', function () {
    try {
        view('blade-exception-utf8')->render();
    } catch (ViewException $exception) {
        $this->assertSame(11, $exception->getLine());
    }
});

it('adds additional blade information to the exception', function () {
    $viewData = [
        'app' => 'foo',
        'data' => true,
        'user' => new User(),
    ];

    try {
        view('blade-exception', $viewData)->render();
    } catch (ViewException $exception) {
        $this->assertSame($viewData, $exception->getViewData());
    }
});

it('adds base exception solution to view exception', function () {
    try {
        $exception = new ExceptionWithSolution;
        view('solution-exception', ['exception' => $exception])->render();
    } catch (ViewException $exception) {
        $this->assertTrue($exception instanceof ViewExceptionWithSolution);
        $this->assertInstanceOf(Solution::class, $exception->getSolution());
        $this->assertSame('This is a solution', $exception->getSolution()->getSolutionTitle());
    }
});

it('detects php view exceptions', function () {
    $this->expectException(ViewException::class);

    view('php-exception')->render();
});

// Helpers
function getSolution(): Solution
{
    return BaseSolution::create('This is a solution')
        ->setSolutionDescription('With a description');
}
