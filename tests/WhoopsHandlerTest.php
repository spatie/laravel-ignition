<?php

use Illuminate\Support\Facades\Route;

uses(TestCase::class);

it('uses a custom whoops handler', function () {
    config()->set('app.debug', true);

    Route::get('exception', function () {
        whoops();
    });

    $result = $this->get('/exception');

    $this->assertTrue(is_string($result->getContent()));
});
