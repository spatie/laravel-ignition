<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

uses(TestCase::class);

beforeEach(function () {
    config()->set('app.debug', true);

    Route::get('will-fail', function () {
        throw new Exception('My exception');
    });
});

test('when requesting html it will respond with html', function () {
    $response = $this
        ->get('will-fail')
        ->baseResponse;

    expect($response->headers->get('Content-Type'))->toStartWith('text/html');
    expect(Str::contains($response->getContent(), 'html'))->toBeTrue();
});

test('when requesting json it will respond with json', function () {
    /** @var \Illuminate\Http\Response $response */
    $response = $this->getJson('will-fail');

    expect($response->headers->get('Content-Type'))->toStartWith('application/json');
    expect(json_decode($response->getContent(), true)['message'])->toEqual('My exception');
});
