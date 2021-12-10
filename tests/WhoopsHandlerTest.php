<?php

use Illuminate\Support\Facades\Route;

it('uses a custom whoops handler', function () {
    config()->set('app.debug', true);

    Route::get('exception', function () {
        whoops();
    });

    $result = $this->get('/exception');

    expect(is_string($result->getContent()))->toBeTrue();
});
