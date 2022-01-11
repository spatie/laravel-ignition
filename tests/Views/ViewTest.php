<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

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
